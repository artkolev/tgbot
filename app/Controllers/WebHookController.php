<?php

declare(strict_types=1);

namespace TGBot\Controllers;

use Longman\TelegramBot\Exception\TelegramException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class WebHookController
 *
 * @author akolevatov
 */
class WebHookController extends BaseController
{

    protected $dataArr = [];

    public function __construct()
    {
        parent::__construct();

        $this->dataArr = $this->getParametersFromRequest();
    }

    /**
     * @param string $token
     * @return JsonResponse
     */
    public function hook(string $token): JsonResponse
    {
        if ($token !== getenv('BOT_API_KEY')) {
            return new JsonResponse(['error' => 'Invalid token'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $telegramIpRanges = [
            ['lower' => '127.0.0.0', 'upper' => '127.255.255.255'], //loopback
            ['lower' => '10.0.0.0', 'upper' => '10.255.255.255'], //local NET
            ['lower' => '100.64.0.0', 'upper' => '100.127.255.255'], //local NET
            ['lower' => '172.16.0.0', 'upper' => '172.31.255.255'], //local NET
            ['lower' => '192.168.0.0', 'upper' => '192.168.255.255'], //local NET

            ['lower' => '92.255.230.89', 'upper' => '92.255.230.89'], //IP tradesoft 92.255.230.89

            ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], //literally 149.154.160.0/20
            ['lower' => '91.108.4.0', 'upper' => '91.108.7.255'], //literally 91.108.4.0/22
        ];

        //Проверка валидности IP
        $ipDec = ip2long($this->request->getClientIp());
        $legalIP = false;

        foreach ($telegramIpRanges as $range) {
            if ($ipDec >= ip2long($range['lower']) && ($ipDec <= ip2long($range['upper']))) {
                $legalIP = true;
                break;
            }
        }

        if (!$legalIP) {
            $this->logger->error(sprintf('Incorrect IP! %s', $this->request->getClientIp()));
            return new JsonResponse(['error' => 'Incorrect IP'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->logger->debug('webhook request', (array)$this->dataArr);

        try {
            $this->telegram->handle();
        } catch (TelegramException $e) {
            $this->logger->error(sprintf('webhook error %s', $e->getMessage()));
        }
        $this->logger->debug(sprintf('command success'));
        return new JsonResponse(['result' => 'OK']);
    }
}
