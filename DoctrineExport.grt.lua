--
-- MySQL Workbench Doctrine Export Plugin
-- Version: 0.4.2dev
-- Authors: Johannes Mueller, Karsten Wutzke
-- Copyright (c) 2008-2010
--
-- http://code.google.com/p/mysql-workbench-doctrine-plugin/
--
-- * The export plugin allows you to export a catalog as Doctrine YAML schema.
-- * This plugin was tested with MySQL Workbench 5.1.18a (JM) and 5.2.4a (KW)
--
-- This file is free software: you can redistribute it and/or
-- modify it under the terms of the GNU Lesser General Public
-- License as published by the Free Software Foundation, either
-- version 3 of the License, or (at your option) any later version.
--
-- This library is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
-- Lesser General Public License for more details.
--
-- You should have received a copy of the GNU Lesser General Public
-- License along with this library.  If not, see <http://www.gnu.org/licenses/>.
--
---------------------------------------------------------------------------------------------
--
-- Special thanks to:
--    Daniel Haas who develops the MySQL Workbench Propel export plugin
--    and kindly agreed to adopt pieces of his source
--    http://www.diloc.de/blog/
--
---------------------------------------------------------------------------------------------
--
-- * IMPORTANT:
-- * If you find BUGS in this plugin or have ideas for IMPROVEMENTS or PATCHES, do not hesitate
-- * to contact us at http://code.google.com/p/mysql-workbench-doctrine-plugin/
--
-- INSTALLATION:
-- Just copy this file into the \modules folder of your
--  a. up until Workbench version 5.0.x installation directory or
--  b. starting with Workbench 5.1.x local user MySQL appdata directory
--
-- USAGE:
-- 1. Open MySQL Workbench
-- 2. Open the database schema
-- 3. Go to "Plugins" -> "Catalog"
--    3a. Select "Doctrine Export: Copy [...] to Clipboard" to save the YAML output to your
--        OSs clipboard, just open a new file and paste it there
--    3b. Select "Doctrine Export: Write [...] to File..." and save the YAML output to a text
--        file as specified in the dialog
--
-- NOTES:
-- 1. The YAML file extension usually is ".yml"
-- 2. The plugin locations from MySQL Workbench changed from version 5.0.x to 5.1.x.
-- 3. On Windows XP the Workbench:copyToClipboard function seems to have a defect in version
--    5.1.9 up to 5.2.1alpha (other versions untested): http://bugs.mysql.com/bug.php?id=44461
-- 4. To change the schema character set and collation in Workbench 5.1.x you have to go to tab
--    "MySQL Model" -> "Physical Schemata", then double click that yellow DB image with the
--    schema name next to it.
--
-- CHANGELOG:
-- 0.4.2dev (JM, KW)
--    + [fix] issue #51 file export overwrites existing file
--            http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=51
--    + [add] implemented mapClass for Aerial
--    + [add] issue #50 concerning options in comments
--            http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=50
--    + [fix] issue #55 concerning external relations
--            http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=55
--    + [add] implemented mapClass for Aerial
--            see http://groups.google.com/group/mysql-workbench-doctrine-plugin/browse_thread/thread/a74ec192ef8200a4
--    + [fix] fixed problem with relations (fixed issue 41)
--    + [add] added type one|many for relations, see issue #39
--    + [add] option for using reference names as relation names instead of foreign table names, see issue #37
--    + [fix] fixed singular/plural issue with table names ending with "us", see issue #33 (KW)
--    + [add] config option for sorting table names alphabetical see issue #34
--    + [add] added {doctrine:externalRelation /} for bypassing relations see issue #32
--    + [add] added m:n relations indications (experimental)
--    + [add] allow relation naming with {doctrine:localAlias} and {doctrine:foreignAlias} in FK comment
--    + [add] allow entity name override with {doctrine:entityName}
--    + [add] interpret TINY/SMALL/MEDIUM prefix in userTypes as length indication
--    + [add] support for fixed-length fields
--    + [fix] remove default userTypes from type conversion table (handled as userTypes)
--    + [fix] fixed problem with cross database joins
--    + [add] optional separate config file
--    + [imp] added more custom options (CUSTOM CONFIG OPTIONS)
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/wiki/HowToUseConfigOptions
--    + [imp] changed behavior of casting userTypes
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=27
-- 0.4.1 (JM)
--    + [imp] export collation, charset and storage type on table level only if explicitly set
--    + [fix] global setting of collation
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=23
--    + [fix] fixed scale issue with decimal type
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=22
-- 0.4.0 (JM)
--    + [add] support for
--              doctrine:foreignAliasOne,
--              doctrine:foreignAliasMany and
--              doctrine:foreignAlias fallback
--            on table comments for custom relation naming
--    + [add] support of doctrine behaviours via table comments
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/wiki/HowToAddDoctrineBehavioursToTheWorkbenchModel
--    + [fix] charset and collate does not work with global options definition
--            changed to work within table focus
--    + [fix] changed export of foreign keys for doubled 1:n relations
--            (e.g. Message -> Sender/Recipient) thanks to Mickael Kurmann for the code snippet
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=18
-- 0.3.9 (KW)
--    + [imp] foreignAliases now considering the cardinality one or many. if one is found,
--            a singular foreignAlias is created, if many is found a pluralized foreignAlias
--            is created
-- 0.3.8 (JM, KW)
--    + [add] added mapping of type YEAR -> integer(2)
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=12
--    + [fix] removed the renameIdColumns function that worked with the (bad) Workbench default
--            primary key and associative table naming conventions to be used with the Doctrine
--            "detect_relations" option
--            see the plugin Wiki page
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=11
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=15
--    + [fix] removed binary flag for columns -> not supported by doctrine
-- 0.3.7 (KW, JM)
--    + [fix] changed conversion of INTEGER from integer to integer(4)
--    + [fix] changed conversion of BLOB types from clob(n) to blob(n)
--            see version 0.3.5 notes
--    + [add] added DEC and NUMERIC to output decimal
--    + [fix] now allowing DECIMAL, DEC, and NUMERIC to be specified with optional precision and
--            scale
--    + [imp] improved the save-to-file routine to work for previously saved files that do not exist
--            anymore (file deleted, renamed, or moved)
--    + [imp] restructured and simplified the supported types code
-- 0.3.6 (JM)
--    + [oth] changed conversion of INT from integer to integer(4)
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=10
-- 0.3.5 (JM)
--    + [fix] type mediumtext | mediumblob -> clob(16777215)
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=9
--    + [add] type longtext   | longblob   -> clob
--            type tinytext   | tinyblob   -> clob(255)
--            type text       | blob       -> clob(65535)
-- 0.3.4 (JM)
--    + [fix] multiple column unique indexes
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=8
--    + [add] preparation for cross database joins (works with MySQL and PostgreSQL and maybe others)
--            please switch the function getCrossDatabaseJoinsFlag() return value to "on" and restart
--            MySQL Workbench (may cause problems with symfony)
--            see http://www.doctrine-project.org/blog/cross-database-joins
-- 0.3.3 (JM)
--    + [add] support for I18n schemes with *_translation tables
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=7
--    + [oth] replaced code indent tabs with spaces
-- 0.3.2 (KW)
--    + [oth] small change in handling version information
-- 0.3.1 (JM)
--    + [fix] changed simple type "INT" to doctrine "integer"
--            see http://code.google.com/p/mysql-workbench-doctrine-plugin/issues/detail?id=6
-- 0.3 (KW)
--    + [fix] types BOOLEAN, BOOL, and INTEGER now working (are no simpleTypes)
--    + [add] lowercasing for default TRUE and FALSE keywords
--    + [imp] default NULL, TRUE, and FALSE detected case-insensitively now (WB does not
--            uppercase default values as opposed to all data types - which are keywords, too)
--    + [add] added version info to module ID and menu items, like this plugins with different
--            versions can be used at the same time (starting with this version and one old)
--    + [imp] file export: added simple functionality to append ".yml" extension to file paths
--            not ending with ".yml"
--    + [imp] removed some unnecessary prints
--    + [imp] shortened changelog entry types [improvement] to [imp] and [other] to [oth]
-- 0.2 (KW)
--    + [add] foreignAlias for relations
--    + [fix] exception thrown in relationBuilding on some tables with foreign keys where the
--            column and referenced columns list has zero length
--    + [imp] replaced all string.len() calls with the length operator #s
--    + [add] function string.endswith()
--    + [imp] eased the code of function exportYamlSchemaToFile (eliminated double if)
--    + [add] functions to test if a (table) name is plural or singular
--    + [fix] reanimated functionality for (English) plural table names
--    + [add] functionality to adjust special (English) table names ending with "ies" to
--            convert "ie" to "y" ("Countries" -> "Country") and more
--    + [add] data type conversion of integer types and CHAR, BOOLEAN, and BOOL:
--            TINYINT   -> integer(1)
--            SMALLINT  -> integer(2)
--            MEDIUMINT -> integer(3)
--            INT       -> integer
--            INTEGER   -> integer
--            BIGINT    -> integer(8)
--            BOOLEAN   -> boolean
--            BOOL      -> boolean
--            CHAR      -> string + fixed option
--    + [add] option for CHAR columns
--    + [imp] removed using the table name capitalization function (ucfirst) from
--            function buildTableName(), like this tables retain their original names and the
--            default Workbench naming convention "_has_" still gets handled correctly
--    + [imp] replaced "\r\n" line endings with "\n" only
--    + [imp] using lowercase for default null values
--    + [imp] restructured MySQL plugin init code for easier understanding
-- 0.1alpha9
--    + [add] function to save to file
--    + [add] print version name on execution in debug window
-- 0.1alpha8
--    + [fix] changed behavior of table renaming (thanks to Francisco Ernesto Teixeira)
--            taBleNaMe -> Tablename ->[fix]-> TaBleNaMe
-- 0.1alpha7
--    + [oth] changed the license from GPLv2 to LGPLv3
--    + [fix] removed plural correction of table names (deprecated in Doctrine 1.0)
-- 0.1alpha6
--    + [fix] some conversion from workbench type to Doctrine type
--            BIGINT   -> INTEGER
--            DATETIME -> TIMESTAMP
--    + [fix] decimal (precision + scale)
--    + [fix] enum handling
-- 0.1alpha5 by quocbao (qbao.nguyen@gmail.com)
--    + [fix] some conversion from workbench type to Doctrine type
--    + [fix] removed generate_accessors [deprecated]
-- 0.1alpha4
--    + [add] tables_has_names -> TablesName ->[fix]-> TableName
--    + [add] rename columns "idtable | table_idtable" -> "id | table_id"
-- 0.1alpha3
--    + [add] convert underscores in tablenames to CamelCase
-- 0.1alpha2
--    + [add] nested set support for tablenames ending with _ns
-- 0.1alpha1
--    supports:
--    + [add] indexes [fulltext, unique, index]
--    + [add] index length
--    + [add] collation
--    + [add] character set
--    + [add] engine [MySQL, InnoDB]
--    + [add] relations
--    + [add] foreign key constraints
--    + [add] table name fixing
--    + [add] column flags [binary, zerofill, unsigned]
--    + [add] autoincrement
--    + [add] not null
--    + [add] default values
--    + [add] decimal precision
--    + [add] column length [e.g. varchar(255)]
--
----------------------------------------------------------------------------------------------

-- standard plugin functions
--
-- this function is called first by MySQL Workbench core to determine number of
-- plugins in this module and basic plugin info
-- see the comments in the function body and adjust the parameters as appropriate
--
function getModuleInfo()

    -- module properties
    local props =
        {
            -- module name (ID)
            name = "DoctrineExport",

            -- module author(s)
            author = "various",

            --module version
            version = "0.4.2dev",

            -- interface implemented by this module
            implements = "PluginInterface",

            -- plugin functions exposed by this module
            -- l looks like a parameterized list, i instance, o object, @ fully qualified class name
            functions =
                {
                    "getPluginInfo:l<o@app.Plugin>:",
                    "exportYamlSchemaToClipboard:i:o@db.Catalog",
                    "exportYamlSchemaToFile:i:o@db.Catalog"
                }
        }

    -- can't assign inside declaration
    props.name = props.name .. props.version

    return props
end

function objectPluginInput(type)

    return grtV.newObj("app.PluginObjectInput", {objectStructName = type})

end

function getPluginInfo()

    -- list of plugins this module exposes (list object of type app.Plugin?)
    local l = grtV.newList("object", "app.Plugin")

    -- plugin instances
    local plugin

    local props = getModuleInfo()

    -- new plugin: export to clipboard
    plugin = createNewPlugin("wb.catalog.util.exportYamlSchemaToClipboard" .. props.version,
                             "Doctrine Export " .. props.version .. ": Copy Generated Doctrine Schema to Clipboard",
                             props.name,
                             "exportYamlSchemaToClipboard",
                             {objectPluginInput("db.Catalog")},
                             {"Catalog/Utilities", "Menu/Catalog"})

    -- append to list of plugins
    grtV.insert(l, plugin)

    -- new plugin: export to file
    plugin = createNewPlugin("wb.catalog.util.exportYamlSchemaToFile" .. props.version,
                             "Doctrine Export " .. props.version .. ": Write Generated Doctrine Schema to File...",
                             props.name,
                             "exportYamlSchemaToFile",
                             {objectPluginInput("db.Catalog")},
                             {"Catalog/Utilities", "Menu/Catalog"})

    -- append to list of plugins
    grtV.insert(l, plugin)

    return l
end

function createNewPlugin(name, caption, moduleName, moduleFunctionName, inputValues, groups)

    -- create dictionary, Lua seems to handle keys and values right...
    local props =
        {
            name = name,
            caption = caption,
            moduleName = moduleName,
            pluginType = "normal",
            moduleFunctionName = moduleFunctionName,
            inputValues = inputValues,
            rating = 100,
            showProgress = 0,
            groups = groups
        }

    local plugin = grtV.newObj("app.Plugin", props)

    -- set owner???
    plugin.inputValues[1].owner = plugin

    return plugin
end

-- ############################
-- ##    change from here    ##
-- ############################

--
-- helper function to check whether
-- a module is loadable
function module_exists(module)
    return pcall(require, module)
end

--
-- function to merge tables
function mergeTables(t1, t2)
  local r = {}
  for k, v in pairs(t1) do
    r[k] = v
  end
  for k, v in pairs(t2) do
    r[k] = v
    print("global config chosen for option " .. k .. "\n")
  end
  return r
end

-- helper function to load
-- config
function loadConfig()
    -- load optional configuration file
    -- %PROGRAMFILES%\MySQL\MySQL Workbench xx\modules\doctrinePluginConfig.lua
    -- appdataDir = os.getenv('APPDATA')
    -- package.path(appdataDir .. '/MySQL/Workbench/')
    if( module_exists("modules.doctrinePluginConfig") ~= false ) then
      require "modules.doctrinePluginConfig"
     -- _G.config = modules.doctrinePluginConfig.Config
    else
      print("optional external config not found\n\n")
    end

    -- #############################
    -- ##  CUSTOM CONFIG OPTIONS  ##
    -- #############################
    _G.locConfig = {
      enableCrossDatabaseJoins            = false    -- default false| true (for further information see blog post on
                                                     -- http://www.doctrine-project.org/blog/cross-database-joins )
      ,defaultStorageEngine               = "InnoDB" -- default InnoDB|MyISAM
      ,enableStorageEngineOverride        = false    -- default false|true
      ,enableOptionsHeader                = true     -- default true|false (enable header output)
      --,enableRenameIdColumns              = true     -- default true|false (detect_relations feature of doctrine)
      ,enableRenameUnderscoresToCamelcase = true     -- default true|false (enable table_name -> tableName)
      ,enableRecapitalizeTableNames       = "first"  -- default first|all|none
      --,enableSingularPluralization        = true     -- default true|false
      --,enableShortFormatting              = false    -- default false|true
      ,preventTableRenaming               = false    -- default false|true
      ,preventTableRenamingPrefix         = "col_"   -- default "col_"
      ,alwaysOutputTableNames             = false    -- default false|true (always add tableName: to table definition)
      ,sortTablesAlphabetical             = false    -- default false|true
      ,useReferencenamesAsRelationnames   = false    -- default false|true
    }

    if ( extConfig ~= nil ) then
      _G.config = mergeTables(locConfig, extConfig)
      print("external config loaded\n\n")
    else
      _G.config = locConfig
      print("local config loaded\n\n")
    end
end

--
-- Print some version information and copyright to the output window
function printVersion()
    print("\n\n\nDoctrine Export v" .. getModuleInfo().version .. "\nCopyright (c) 2008 - 2010 Johannes Mueller, Karsten Wutzke - License: LGPLv3\n\n");
    loadConfig()
end

--
-- Convert workbench simple types to doctrine types
function wbSimpleType2DoctrineDatatype(prefix, column)
    local conversionTable = {
        ["VARCHAR"]      = "string",
        ["CHAR"]         = "string",
--        ["CHARACTER"]    = "string",
--        ["INT1"]         = "integer(1)",
        ["TINYINT"]      = "integer(1)",
--        ["INT2"]         = "integer(2)",
        ["SMALLINT"]     = "integer(2)",
--        ["INT3"]         = "integer(3)",
        ["MEDIUMINT"]    = "integer(3)",
--        ["INT4"]         = "integer(4)",
        ["INT"]          = "integer(4)",
--        ["INTEGER"]      = "integer(4)",
--        ["INT8"]         = "integer(8)",
        ["BIGINT"]       = "integer(8)",
--        ["DEC"]          = "decimal",
        ["DECIMAL"]      = "decimal",
--        ["NUMERIC"]      = "decimal",
        ["FLOAT"]        = "float",
        ["DOUBLE"]       = "float",
        ["DATE"]         = "date",
        ["TIME"]         = "time",
        ["DATETIME"]     = "timestamp",
        ["TIMESTAMP"]    = "timestamp",
        ["YEAR"]         = "integer(2)",
--        ["BOOL"]         = "boolean",
--        ["BOOLEAN"]      = "boolean",
        ["BINARY"]       = "binary",      -- internally Doctrine seems to map binary to blob, which is wrong
        ["VARBINARY"]    = "varbinary",   -- internally Doctrine seems to map varbinary to blob, which is OK
        ["TINYTEXT"]     = "clob(255)",
        ["TEXT"]         = "clob(65535)",
        ["MEDIUMTEXT"]   = "clob(16777215)",
--        ["LONG"]         = "clob(16777215)",
--        ["LONG VARCHAR"] = "clob(16777215)",
        ["LONGTEXT"]     = "clob",
        ["TINYBLOB"]     = "blob(255)",
        ["BLOB"]         = "blob(65535)",
        ["MEDIUMBLOB"]   = "blob(16777215)",
        ["LONGBLOB"]     = "blob",
        ["ENUM"]         = "enum"
    }

    local validUserTypesTable = {
        ['BOOL'] = 'boolean',
        ['BOOLEAN'] = 'boolean'
    }

    local fixedTypesTable = {
        ['BINARY'] = true,
        ['CHAR'] = true
    }

    local sizePrefixes = {
        ['TINY']   = 255,
        ['SMALL']  = 65535,
        ['MEDIUM'] = 16777215
    }

    local nativeStubType = 'MULTIPOLYGON'

    local typeName = nil
    local doctrineType = nil
    local fixed = nil
    local length = column.length
    local res = ""

    -- assign typeName with simpleType or userType (structuredType will not be supported anytime soon)
    if ( column.simpleType ~= nil ) then
        typeName = column.simpleType.name
        fixed = fixedTypesTable[(typeName)]
    elseif ( column.userType ~= nil ) then
        if ( validUserTypesTable[(column.userType.name)] ~= nil ) then
            doctrineType = validUserTypesTable[(column.userType.name)]
            typeName = "irrelevant"
        elseif ( column.userType.actualType.name == nativeStubType ) then
            typeName = "irrelevant"
            doctrineType = column.userType.name
            local p,l
            for p,l in pairs(sizePrefixes) do
                if string.sub(doctrineType,1,#p) == p then
                    length = l
                    doctrineType = string.sub(doctrineType,#p+1)
                    break
                end
            end
            if string.sub(doctrineType,1,1) == "_" then
                doctrineType = string.gsub( doctrineType, "_", "\\" )
            end
        else
            typeName = column.userType.actualType.name
            fixed = fixedTypesTable[(typeName)]
        end
    elseif ( column.structuredType ~= nil ) then
        -- print("\n" .. column.name .. " type = " .. column.structuredType.name)
        return "structuredType (not implemented)"
    end

    -- print("\n" .. column.name .. " type = " .. typeName)

    -- grab conversion type
    if ( doctrineType == nil ) then
      doctrineType = conversionTable[(typeName)]

      if ( doctrineType == nil ) then
          -- expr and a or b is LUA ternary operator fake
          doctrineType = "unsupported " .. (column.simpleType == nil and "simpleType" or "userType" ) .. " " .. typeName
      end
      end

    -- begin with type
    res = prefix .. "type: " .. ((doctrineType == nil) and "unknown" or doctrineType)

    -- add length/precision/scale
      if ( doctrineType == "decimal" ) then
          if ( column.precision ~= nil and column.precision ~= -1 ) then
              -- append precision in any case
            res = res .. "(" .. column.precision .. ")\n"
              -- append optional scale (only possible if precision is valid)
              if ( column.scale ~= nil and column.scale ~= -1 ) then
                res = res .. prefix .. "scale: " .. column.scale
              end
              -- close parentheses
              -- doctrineType = doctrineType .. ")"
          end
    elseif ( length ~= -1 ) then
        res = res .. "(" .. length .. ")"
      end
    res = res  .. "\n"

    -- add enum values
    if ( doctrineType == "enum" ) then
        local s = column.datatypeExplicitParams
        res = res .. prefix .. "values: ["
        res = res .. string.sub(s, 2, #s - 1)
        res = res .. "]\n"
    end

    -- add fixed flag
    if ( fixed ~= nil ) then
        res = res .. prefix .. "fixed: true\n"
    end
    
    -- add validator
    local validators = getCommentToken( column.comment, "validators" )
    if( validators ~= nil ) then
      res = res .. validators .. "\n"
    end

    return res
end

--
-- converts first character of given string to uppercase
function ucfirst(s)
    -- only capitalize the very first char, leave all others untouched
    return string.upper(string.sub(s, 0, 1)) .. string.sub(s, 2, #s)

    -- old: lowers rest for whatever reason
    --return string.upper(string.sub(s, 0, 1)) .. string.lower(string.sub(s, 2, #s))
end

--
-- converts first character of given string to lowercase
function lcfirst(s)
    -- only lowercase the very first char, leave all others untouched
    return string.lower(string.sub(s, 0, 1)) .. string.sub(s, 2, #s)
end

--
-- converts a table_name to tableName
function underscoresToCamelCase(s)
  if ( config.enableRenameUnderscoresToCamelcase ~= true ) then
    return s
  end
  s = string.gsub(s, "_(%w)", function(v)
        return string.upper(v)
      end)
  return s
end

function getTable(tables, name)
    local k
    for k = 1, grtV.getn(tables) do
        if ( name == tables[k].name ) then
            return tables[k]
        end
    end
end
--
-- changing tableNames of workbench into
-- doctrine friendly entityNames
function buildEntityName(table)
    local s
    s = getCommentToken( table.comment, "entityName" )

    if ( s == nil ) then
        s = table.name

    if ( config.enableRecapitalizeTableNames == "first" ) then
      s = ucfirst(s)
    end

    if ( isNestedTableModel(s) ) then
        s = string.sub(s, 1, #s - 3)
    end
    --
    -- converting User_has_Groups (default WB-Scheme) to UserGroups
    -- as used in the doctrine manual
    local patternStart, patternEnd = string.find(s, "_has_")
    if ( patternStart ~= nil and patternEnd ~= nil ) then
        local front = singularizeTableName(string.sub(s, 1, patternStart - 1))
        local back = string.sub(s, patternEnd + 1)
        s = ucfirst(front) .. ucfirst(back)
    end

    s = singularizeTableName(s)

    --
    -- make camel_case to CamelCase
    s = underscoresToCamelCase(s)
    end

    return s
end

-- extend string functionality
function string.endswith(s, suffix)
    return s:sub(#s - #suffix + 1) == suffix
end

function isPlural(s)
    -- is plural if string ends with an "s" but not with "ss"
    return #s > 1 and string.endswith(s, "s") and not string.endswith(s, "ss") and not string.endswith(s, "us")
end

function isSingular(s)
    -- is singular if not plural
    return not isPlural(s)
end

--
-- remove plural of tableNames
-- Groups becomes Group
function singularizeTableName(s)
    if ( config.preventTableRenaming ) then
      return s
    end

    -- is plural?
    if ( isPlural(s) ) then
        -- strip "s"
        s = string.sub(s, 1, #s - 1)

        -- we can't just strip the s without looking at the remaining English plural endings
        -- see http://en.wikipedia.org/wiki/English_plural

        -- if the table name ends with "e" ("coache", "hashe", "addresse", "buzze", "heroe", ...)
        if (    string.endswith(s, "che")
             or string.endswith(s, "she")
             or string.endswith(s, "sse")
             or string.endswith(s, "zze")
             or string.endswith(s, "oe") ) then

            -- strip an "e", too
            s = string.sub(s, 1, #s - 1)

        -- if table name ends with "ie"
        elseif ( string.endswith(s, "ie") ) then
            -- replace "ie" by a "y" ("countrie" -> "country", "hobbie" -> "hobby", ...)
            s = string.sub(s, 1, #s - 2) .. "y"

        elseif ( string.endswith(s, "ve") ) then
            -- replace "ve" by an "f" ("calve" -> "calf", "leave" -> "leaf", ...)
            s = string.sub(s, 1, #s - 2) .. "f"

            -- does *not* work for certain words ("knive" -> "knif", "stave" -> "staf", ...): TODO (hard)
        else
            -- do nothing ("game", "referee", "monkey", ...)

            -- note: table names like "Caches" can't be handled correctly because of the "che" rule above,
            -- that word however basically stems from French and might be considered a special case anyway
            -- also collective names like "Personnel", "Cast" (caution: SQL keyword!) can't be singularized
        end
    end

    return s
end

function pluralizeTableName(s)
    if ( config.preventTableRenaming ) then
      return s
    end

    -- is singular?
    if ( isSingular(s) ) then

        -- we can't just append the s without looking at the English singular endings
        -- see http://en.wikipedia.org/wiki/English_plural

        -- if the table name ends with "ch", "sh", "ss" or "zz" ("coach", "hash", "address", "buzz", "hero", ...)
        if (    string.endswith(s, "ch")
             or string.endswith(s, "sh")
             or string.endswith(s, "ss")
             or string.endswith(s, "zz")
             or string.endswith(s, "o") ) then

            -- append "es"
            s = s .. "es"

        -- if table name ends with "y"
        elseif ( string.endswith(s, "y") ) then
            -- replace "y" with "ies" ("country" -> "countries", "hobby" -> "hobbies", ...)
            s = string.sub(s, 1, #s - 1) .. "ies"

        elseif ( string.endswith(s, "f") ) then
            -- replace "f" by an "ves" ("leaf" -> "leaves", "half" -> "halves", ...)
            s = string.sub(s, 1, #s - 1) .. "ves"
        else
            -- append "s" ("games", "referees", "monkeys", ...)
            s = s .. "s"
        end
    end

    return s
end

--
-- checks if a given string ends with _ns
-- which means it is a NestedSet Table
function isNestedTableModel(s)
    if ( string.sub(s, #s - 2 ) == '_ns' ) then
        return true
    end
    return false
end

--
-- building yaml for relations of a given
-- foreignKey
function relationBuilding(tbl, tables)

    local i, k
    local foreignKey = nil
    -- local relations = "  relations:\n"
    local relations = ""
    local relName
    local foreignClass
    local foreignAlias
    local mnRelations
    local localAlias
    local refClass
    local localClass = buildEntityName(tbl)

    for k = 1, grtV.getn(tbl.foreignKeys) do
        foreignKey = tbl.foreignKeys[k]
        foreignClass = buildEntityName(foreignKey.referencedTable)

        relName = getCommentToken(foreignKey.comment, "localAlias")
        if( relName == nil or relName == "" ) then
            relName = getCommentToken(foreignKey.referencedTable.comment, "foreignAliasOne")
            if( relName == nil or relName == "" and #foreignKey.columns > 0 ) then
               relName = foreignKey.columns[1].name
               if (string.endswith(relName, "_id")) then
                   -- relName = string.sub(relName, 1, #relName - 2)
                   relName = string.sub(relName, 1, #relName - 3)
               end
            end
        end

        -- use the name of the reference as relation name
        if ( config.useReferencenamesAsRelationnames ) then
          relName = foreignKey.name
          if ( config.enableRenameUnderscoresToCamelcase ) then
            relName = underscoresToCamelCase(relName)
          end
          if ( config.enableRecapitalizeTableNames == "first" ) then
            relName = ucfirst(relName)
          end
        -- else use the name of the foreign class as relation name
        else
          relName = lcfirst(underscoresToCamelCase(foreignClass))
        end
        relations = relations .. "    " .. relName .. ":\n"
        relations = relations .. "      class: " .. foreignClass .. "\n"
        relations = relations .. "      local: " .. foreignKey.columns[1].name .. "\n"
        relations = relations .. "      foreign: " .. foreignKey.referencedColumns[1].name .. "\n"

        foreignAlias = getCommentToken(foreignKey.comment, "foreignAlias")
        
        if ( foreignAlias == nil or foreignAlias == "" ) then
            -- 1:1 FK creates singular, 1:n creates plural Doctrine foreignAlias -> getEmailAdresses(), getContact(), ...
            if ( foreignKey.many == 1 ) then
                foreignAlias = getCommentToken(tbl.comment, "foreignAliasMany")
            else
                foreignAlias = getCommentToken(tbl.comment, "foreignAliasOne")
            end
        end
        if ( foreignAlias == nil or foreignAlias == "" ) then
            foreignAlias = getCommentToken(tbl.comment, "foreignAlias")
        end
        if ( foreignAlias == nil or foreignAlias == "" ) then
            if ( foreignKey.many == 1 ) then
                if ( config.preventTableRenaming ) then
                    -- foreignAlias = config.preventTableRenamingPrefix .. pluralizeTableName(foreignClass)
                    foreignAlias = config.preventTableRenamingPrefix .. pluralizeTableName(localClass)
                else
                    -- foreignAlias = lcfirst(pluralizeTableName(foreignClass))
                    foreignAlias = lcfirst(pluralizeTableName(localClass))
                end
            elseif ( foreignKey.many == 0 ) then
                -- foreignAlias = lcfirst(foreignClass)
                foreignAlias = lcfirst(localClass)
            else
                foreignAlias = "FK " .. foreignKey.name .. " is broken! It has no destination cardinality (many is not 0 and not 1)."
            end
        end

        relations = relations .. "      foreignAlias: " .. foreignAlias .. "\n"

        if ( foreignKey.deleteRule ~= nil and foreignKey.deleteRule ~= "" and foreignKey.deleteRule ~= "NO ACTION" ) then
            relations = relations .. "      onDelete: " .. string.lower( foreignKey.deleteRule ) .. "\n"
        end

        if ( foreignKey.updateRule ~= nil and foreignKey.updateRule ~= "" and foreignKey.updateRule ~= "NO ACTION" ) then
            relations = relations .. "      onUpdate: " .. string.lower( foreignKey.updateRule ) .. "\n"
        end

        if( foreignKey.many == 1 ) then
          relations = relations .. "      foreignType: many\n"
        else
          relations = relations .. "      foreignType: one\n"
        end
        relations = relations .. "      owningSide: true\n"
    end

    -- add m:n relations
    mnRelations = getCommentToken(tbl.comment, "mnRelations")
    if ( mnRelations ~= nil ) then
        for localAlias, refClass, foreignClass, foreignAlias in string.gmatch(mnRelations, "(%a+)=>([%a%d_]+)=>([%a%d_]+)=>(%a+)") do
            refClass = getTable( tables, refClass )
            foreignClass = getTable( tables, foreignClass )
            relations = relations .. "    " .. localAlias .. ":\n"
            relations = relations .. "       class: " .. buildEntityName( foreignClass ) .. "\n"
            relations = relations .. "       refClass: " .. buildEntityName( refClass ) .. "\n"
            relations = relations .. "       foreignAlias: " .. foreignAlias .. "\n"
        end
    end

    -- allow for defining extra relations not included in model diagram
    -- (e.g. for relations with tables from Symfony plugins schema files)
    local externalRelations = nil
    externalRelations = getCommentToken(tbl.comment, "externalRelations")
    if ( externalRelations ~= "" and externalRelations ~= nil ) then
        relations = relations .. externalRelations .. "\n"
    end

    -- if ( foreignKey ~= nil or mnRelations ~= nil ) then
    if ( relations ~= "" ) then
        return "  relations:\n" .. relations
    end

    return ""
end

--
-- extract informations regarding doctrine
-- from table comments in workbench model
-- comment like {doctrine:actAs} [..] {/doctrine:actAs}
function getCommentToken(c, info)
  local tmp
  tmp = string.gsub(c, ".*{doctrine:" .. info .. "}(.+){/doctrine:" .. info .. "}.*", function(v)
            return string.gsub(v, "^[\r\n]*(.+)", function(x)
                return string.gsub(x, "[\r\n]*$", "")
              end)
          end)
  if ( tmp == c ) then
    return nil
  end
  return tmp
end

--
-- check for *_translation table
-- related to I18n Support in doctrine
function hasTranslationTableModel(tblname, tables)
    local tmp
    tmp = getTranslationTableModel(tblname, tables)
    if ( tmp ~= nil ) then
      return true
    end
    return false
end

--
-- returns a reference of the translation table
-- by given table name
function getTranslationTableModel(tblname, tables)
    tblname = tblname .. "_translation"
    return getTableModel(tblname, tables)
end

function getTableModel(tblname, tables)
    local k
    for k = 1, grtV.getn(tables) do
        if ( tblname == tables[k].name ) then
            return tables[k]
        end
    end
    return nil
end

--
-- build a list of the I18n fields by a given
-- table name works only if *_translation table of
-- given tblname exist
function buildActAsI18nFieldsList(tblname, tables)
    local k, l
    local returnText = "      fields: ["
    tblname = tblname .. "_translation"
    for k = 1, grtV.getn(tables) do
        if ( tblname == tables[k].name ) then
            for l = 1, grtV.getn(tables[k].columns) do
                col = tables[k].columns[l]
                if ( col.name ~= "id" and col.name ~= "lang" ) then
                    returnText = returnText .. col.name .. ", "
                end
            end
        end
    end
    returnText = string.sub(returnText, 1, #returnText - 2)
    returnText = returnText .. "]\n"
    return returnText
end

--
-- generates the yaml schema
function generateYamlSchema(cat)
    -- load schema
    local i, j, schema, tbl
    local yaml = "---\n"

    for i = 1, grtV.getn(cat.schemata) do
        schema = cat.schemata[i]

        --print(schema)

        if ( config.enableOptionsHeader ) then
            -- automatically detect relations
            yaml = yaml .. "detect_relations: true\n"
            --
            -- set basic options
            yaml = yaml .. "options:\n"
            if ( schema.defaultCollationName ~= nil and schema.defaultCollationName ~= "" ) then
                yaml = yaml .. "  collate: " .. schema.defaultCollationName .. "\n"
            end
            if ( schema.defaultCharacterSetName ~= nil and schema.defaultCharacterSetName ~= "" ) then
                yaml = yaml .. "  charset: " .. schema.defaultCharacterSetName .. "\n"
            end
            -- does not exist in WB yet (6.x?)
            -- yaml = yaml .. "  type: " .. schema.defaultStorageEngineName .. "\n"
            --yaml = yaml .. "  type: " .. "InnoDB" .. "\n"
            yaml = yaml .. "  type: " .. config.defaultStorageEngine .. "\n"

            yaml = yaml .. "\n"

            optionsSetFlag = true
        end

        --print(schema)

        local t = {}
        for j = 1, grtV.getn(schema.tables) do
            tbl = schema.tables[j]
            table.insert(t, {key=tbl.name, value=tbl})
        end

        if ( config.sortTablesAlphabetical ) then
            table.sort(t, function(a,b) return a.key < b.key; end);
        end

        for idx, row in ipairs(t) do
            --
            -- do not export *_translation tables
            if ( string.endswith(row.key, "_translation") == false ) then
                yaml = buildYamlForSingleTable(row.value, schema, yaml)
            end
        end
    end

    --print(yaml)

    return yaml
end

function buildYamlForSingleColumn(tbl, col, yaml)
    local l, m

    --
    -- start of adding a column
    yaml = yaml.."    "..col.name..":\n"
    yaml = yaml .. wbSimpleType2DoctrineDatatype("      ", col)
    for m = 1, grtV.getn(tbl.indices) do
        index = tbl.indices[m]
        --
        -- checking for primary index
        if ( index.indexType == "PRIMARY" ) then
            for l = 1, grtV.getn(index.columns) do
                column = index.columns[l]
                if ( column.referencedColumn.name == col.name ) then
                    yaml = yaml .."      primary: true\n"
                end
            end
        end
        --
        -- checking for unique index
        if ( index.indexType == "UNIQUE" ) then
            -- check if just one column in index
            if ( grtV.getn(index.columns) == 1 ) then
                for l = 1, grtV.getn(index.columns) do
                    column = index.columns[l]
                    if ( column.referencedColumn.name == col.name ) then
                        yaml = yaml .. "      unique: true\n"
                    end
                end
            end
        end
    end
    --
    -- setting flags
    if ( col.flags ~= nil ) then
        local flag
        for l = 1, grtV.getn(col.flags) do
            flag = grtV.toLua(col.flags[l])
            if ( flag ~= nil ) then
                if ( flag == "UNSIGNED" ) then
                    yaml = yaml .. "      unsigned: true\n"
                end
                --
                -- not implemented in Doctrine
                -- if ( flag == "BINARY" ) then
                --     yaml = yaml .. "      binary: true\n"
                -- end
                if ( flag == "ZEROFILL" ) then
                    yaml = yaml .. "      zerofill: true\n"
                end
            end
        end
    end
    --
    -- checking for mysql column option not null
    if ( col.isNotNull == 1 ) then
        yaml = yaml.."      notnull: true\n"
    end
    --
    -- checking for default value of a column
    if ( col.defaultValue ~= '' and string.upper(col.defaultValue) ~= 'CURRENT_TIMESTAMP' ) then
        yaml = yaml .. "      default: "

        -- Lua has no switch...
        -- switch ( string.upper(col.defaultValue) )

        -- if null, true, or false then lowercase
        if (    string.upper(col.defaultValue) == "NULL"
             or string.upper(col.defaultValue) == "TRUE"
             or string.upper(col.defaultValue) == "FALSE" ) then
            yaml = yaml .. string.lower(col.defaultValue)
        else
            yaml = yaml .. col.defaultValue
        end
        yaml = yaml .. "\n"
    end
    --
    -- checking for autoincrement of a column
    if ( col.autoIncrement == 1 ) then
        yaml = yaml.."      autoincrement: true\n"
    end

    -- if CHAR type, set fixed flag
    -- commented because of issue #40
    --if ( col.simpleType ~= nil and col.simpleType.name == "CHAR" ) then
    --    yaml = yaml.."      fixed: true\n"
    --end

    return yaml
end

function buildYamlForSingleTable(tbl, schema, yaml)
    local k, l, col, index, column
    local actAsPart = ""
    local actAs = ""
    local package = ""
    local commentOpts = ""
    
    --
    -- start of adding a table
    yaml = yaml .. buildEntityName(tbl) .. ":\n"

    -- check for package: in schema comments needed when using Aerial CMS
	-- this will create the YAML mapClass property for Aerial
	if ( schema.comment ~= nil and schema.comment ~= "" ) then
      package = getCommentToken(schema.comment, "package")
      if ( package ~= "" and package ~= nil ) then
        yaml = yaml .. package .. "." .. tbl.name .. "\n"
      end
    end
    
    -- check for actAs: in table comments
    if ( tbl.comment ~= nil and tbl.comment ~= "" ) then
      actAs = getCommentToken(tbl.comment, "actAs")
      if ( actAs ~= "" and actAs ~= nil ) then
        yaml = yaml .. actAs .. "\n"
      end
      commentOpts = getInfoFromTableComment(tbl.comment, "options")
    end

    -- test singularize and pluralize functions
    --print("\n" .. singularizeTableName(tbl.name))
    --print(" <-> ")
    --print(pluralizeTableName(tbl.name))

    --
    -- add the real table name to the model if
    -- the automatic build table name does not match the mysql
    -- table name
    -- crossDatabaseJoins must be disabled
    if ((    string.lower(buildEntityName(tbl)) ~= string.lower(tbl.name)
          or config.alwaysOutputTableNames
        )
          and not config.enableCrossDatabaseJoins
       ) then
        yaml = yaml .. "  tableName: " .. tbl.name .. "\n"
    end

    -- crossDatabaseJoins enabled
    if ( config.enableCrossDatabaseJoins ) then
        yaml = yaml .. "  tableName: " .. schema.name .. "." .. tbl.name .. "\n"
        yaml = yaml .. "  connection: " .. schema.name .. "\n"
    end

    -- check if table ends with _ns means
    -- NestedSet Model
    if ( isNestedTableModel(tbl.name) ) then
        actAsPart = actAsPart .. "    NestedSet:\n"
    end

    --
    -- check for I18n tables
    if ( hasTranslationTableModel(tbl.name, schema.tables) ) then
        actAsPart = actAsPart .. "    I18n:\n"
        actAsPart = actAsPart .. buildActAsI18nFieldsList(tbl.name, schema.tables)
    end

    --
    -- add ActAs: part to the table model
    if ( string.len(actAsPart) > 0 ) then
        yaml = yaml .. "  actAs:\n"
        yaml = yaml .. actAsPart
    end

    --
    -- iterate through the table columns
    yaml = yaml .. "  columns:\n"
    for k = 1, grtV.getn(tbl.columns) do
        col = tbl.columns[k]
        yaml = buildYamlForSingleColumn(tbl, col, yaml)
    end

    --
    -- hack for adding columns outsourced
    -- to a *_translation table
    if ( hasTranslationTableModel(tbl.name, schema.tables) ) then
        local translationTable
        translationTable = getTranslationTableModel(tbl.name, schema.tables)
        for k = 1, grtV.getn(translationTable.columns) do
            col = translationTable.columns[k]
            if ( col.name ~= "id" and col.name ~= "lang" ) then
                yaml = buildYamlForSingleColumn(tbl, col, yaml)
            end
        end
    end

    --
    -- add foreign keys
    yaml = yaml .. relationBuilding(tbl, schema.tables)
    --
    -- add missing indices
    local indexes = ""
    for k = 1, grtV.getn(tbl.indices) do
        index = tbl.indices[k]
        if ( index.indexType == "INDEX" ) then
            indexes = indexes .. "    " .. index.name .. ":\n"
            indexes = indexes .. "      fields: ["
            for l = 1, grtV.getn(index.columns) do
                column = index.columns[l]
                indexes = indexes .. column.referencedColumn.name
                if ( l < grtV.getn(index.columns) ) then
                    indexes = indexes .. ", "
                end
            end
            indexes = indexes .. "]\n"
            if ( index.keyBlockSize ~= nil and index.keyBlockSize ~= 0 ) then
                indexes = indexes .. "      length: " .. index.keyBlockSize .. "\n"
            end
        end
        if ( index.indexType == "FULLTEXT" ) then
            indexes = indexes .. "    " .. index.name .. ":\n"
            indexes = indexes .. "      fields: ["
            for l = 1, grtV.getn(index.columns) do
                column = index.columns[l]
                indexes = indexes .. column.referencedColumn.name
                if ( l < grtV.getn(index.columns) ) then
                    indexes = indexes .. ", "
                end
            end
            indexes = indexes .. "]\n"
            indexes = indexes .. "      type: fulltext\n"
        end
        if ( index.indexType == "UNIQUE" ) then
            -- check if more than 1 column in index
            -- otherwise ignore
            if( grtV.getn(index.columns) > 1 ) then
                indexes = indexes .. "    " .. index.name .. ":\n"
                indexes = indexes .. "      fields:\n"
                for l = 1, grtV.getn(index.columns) do
                    column = index.columns[l]
                    indexes = indexes .. "        " .. column.referencedColumn.name .. ":\n"
                    -- check if column in index is ASC or DESC
                    if ( column.descend ~= nil and column.descend ~= "" ) then
                        if ( column.descend == 0 ) then
                            indexes = indexes .. "          sorting: ASC\n"
                        else
                            indexes = indexes .. "          sorting: DESC\n"
                        end
                    end
                    -- check for column length in index
                    if ( column.columnLength ~= nil and column.columnLength ~= "" and column.columnLength ~= 0 ) then
                        indexes = indexes .. "          length: " .. column.columnLength .. "\n"
                    end
                end
                indexes = indexes .. "      type: unique\n"
            end
        end
    end
    if ( indexes ~= "" ) then
        yaml = yaml .. "  indexes:\n" .. indexes
    end
    --
    -- add the options
    local options = ""

    if ( tbl.defaultCharacterSetName ~= nil and tbl.defaultCharacterSetName ~= "" ) then
        options = options .. "    charset: " .. tbl.defaultCharacterSetName .. "\n"
    --elseif ( schema.defaultCharacterSetName ~= nil and schema.defaultCharacterSetName ~= "" ) then
    --    options = options .. "    charset: " .. schema.defaultCharacterSetName .. "\n"
    end

    if ( tbl.defaultCollationName ~= nil and tbl.defaultCollationName ~= "" ) then
        options = options .. "    collate: " .. tbl.defaultCollationName .. "\n"
    --elseif ( schema.defaultCollationName ~= nil and schema.defaultCollationName ~= "" ) then
    --    options = options .. "    collate: " .. schema.defaultCollationName .. "\n"
    end

    -- set table engine only if other than global definition of InnoDB
    if ((     tbl.tableEngine ~= nil
          and tbl.tableEngine ~= ""
          and tbl.tableEngine ~= config.defaultStorageEngine
         )
          or config.enableStorageEngineOverride
        ) then
          options = options .. "    type: " .. tbl.tableEngine .. "\n"
    end

    if ( commentOpts ~= "" and commentOpts ~= nil ) then
      options = options .. commentOpts .. "\n"
    end
    
    if ( options ~= "" ) then
        yaml = yaml .. "  options:\n" .. options
    end

    -- final line break
    return yaml .. "\n"
end

---------------------------------------------------------------------------------------------------

-- export function #1
function exportYamlSchemaToClipboard(catalog)

    printVersion()
    local yaml = generateYamlSchema(catalog)

    Workbench:copyToClipboard(yaml)

    print('\n > YAML schema copied to clipboard')

    return 0
end

-- export function #2
function exportYamlSchemaToFile(catalog)

    printVersion()
    local yaml = generateYamlSchema(catalog)
    local file = catalog.customData["doctrineExportPath"]

    --print("\nFilepath is: " .. file)

    if (     file ~= nil
         and Workbench:confirm("Overwrite?", "Do you want to overwrite the previously exported file " .. file .. "?") == 1 
         and io.open(file, "w")) then

        -- global
        doctrineExportPath = file

    else
        doctrineExportPath = Workbench:input("Please enter a path to the file to export the doctrine schema to.", "param1", "param2", "param3", "param4")

        if ( doctrineExportPath ~= "" ) then
            -- Try to save the filepath for the next time:

            -- if file path doesn't end with .yml, append that
            if ( not string.endswith(doctrineExportPath, ".yml") ) then

                if ( string.endswith(doctrineExportPath, ".") ) then
                    doctrineExportPath = doctrineExportPath .. "yml"
                else
                    doctrineExportPath = doctrineExportPath .. ".yml"
                end
            end

            catalog.customData["doctrineExportPath"] = doctrineExportPath
        end
    end

    if ( doctrineExportPath ~= '' ) then

        f = io.open(doctrineExportPath, "w")

        if ( f ~= nil ) then
            f.write(f, yaml)
            f.close(f)
            print('\n > Doctrine schema was written to file ' .. doctrineExportPath)
        else
            print('\n > Could not open file for writing ' .. doctrineExportPath .. '!')
        end
    else
        print('\n > Doctrine schema not exported as no path was given!')
    end

    return 0
end
