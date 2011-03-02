<?php
/**
 * RedBean MySQLWriter
 *
 * @file				RedBean/QueryWriter/MySQL.php
 * @description	Represents a MySQL Database to RedBean
 *						To write a driver for a different database for RedBean
 *						you should only have to change this file.
 * @author			Gabor de Mooij
 * @author          Mickael Desfrenes <desfrenes@gmail.com>
 * @license			BSD
 *
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_QueryWriter_MySQLUUID extends RedBean_QueryWriter_AQueryWriter implements RedBean_QueryWriter
{

    /**
     * Here we describe the datatypes that RedBean
     * Uses internally. If you write a QueryWriter for
     * RedBean you should provide a list of types like this.
     */

    /**
     * @var integer
     *
     * DATA TYPE
     * Boolean Data type
     */
    const C_DATATYPE_BOOL = 0;

    /**
     *
     * @var integer
     *
     * DATA TYPE
     * Unsigned 8BIT Integer
     *
     */
    const C_DATATYPE_UINT8 = 1;

    /**
     *
     * @var integer
     *
     * DATA TYPE
     * Unsigned 32BIT Integer
     *
     */
    const C_DATATYPE_UINT32 = 2;

    /**
     * @var integer
     *
     * DATA TYPE
     * Double precision floating point number and
     * negative numbers.
     *
     */
    const C_DATATYPE_DOUBLE = 3;

    /**
     * @var integer
     *
     * DATA TYPE
     * Standard Text column (like varchar255)
     * At least 8BIT character support.
     *
     */
    const C_DATATYPE_TEXT8 = 4;

    /**
     * @var integer
     *
     * DATA TYPE
     * Long text column (16BIT)
     *
     */
    const C_DATATYPE_TEXT16 = 5;

    /**
     * @var integer
     *
     * DATA TYPE
     * 32BIT long textfield (number of characters can be as high as 32BIT) Data type
     * This is the biggest column that RedBean supports. If possible you may write
     * an implementation that stores even bigger values.
     *
     */
    const C_DATATYPE_TEXT32 = 6;

    /**
     * @var integer
     *
     * DATA TYPE
     * Specified. This means the developer or DBA
     * has altered the column to a different type not
     * recognized by RedBean. This high number makes sure
     * it will not be converted back to another type by accident.
     *
     */
    const C_DATATYPE_SPECIFIED = 99;

    const C_DATATYPE_UUID = 98;

    /**
     * @var array
     * Supported Column Types
     */
    public $typeno_sqltype = array(

        self::C_DATATYPE_UINT8=>" TINYINT(3) UNSIGNED ",
        self::C_DATATYPE_UINT32=>" INT(11) UNSIGNED ",
        self::C_DATATYPE_DOUBLE=>" DOUBLE ",
        self::C_DATATYPE_TEXT8=>" VARCHAR(255) ",
        self::C_DATATYPE_TEXT16=>" TEXT ",
        self::C_DATATYPE_TEXT32=>" LONGTEXT ",
        self::C_DATATYPE_UUID => " BINARY(16) "
    );

    /**
     *
     * @var array
     * Supported Column Types and their
     * constants (magic numbers)
     */
    public $sqltype_typeno = array(
        "set('1')"=>self::C_DATATYPE_BOOL,
        "tinyint(3) unsigned"=>self::C_DATATYPE_UINT8,
        "int(11) unsigned"=>self::C_DATATYPE_UINT32,
        "double" => self::C_DATATYPE_DOUBLE,
        "varchar(255)"=>self::C_DATATYPE_TEXT8,
        "text"=>self::C_DATATYPE_TEXT16,
        "longtext"=>self::C_DATATYPE_TEXT32,
        "binary(16)"=>self::C_DATATYPE_UUID
    );

    /**
     *
     * @var RedBean_Adapter_DBAdapter
     */
    protected $adapter;

    /**
     * @var string
     * character to escape keyword table/column names
     */
    protected $quoteCharacter = '`';

    /**
     * Constructor.
     * The Query Writer Constructor also sets up the database.
     *
     * @param RedBean_Adapter_DBAdapter $adapter adapter
     * @param boolean							$frozen  allow schema modif.?
     *
     *
     */
    public function __construct( RedBean_Adapter $adapter, $frozen = false )
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns all tables in the database.
     *
     * @return array $tables tables
     */
    public function getTables()
    {
        return $this->adapter->getCol( "show tables" );
    }

    /**
     * Creates an empty, column-less table for a bean.
     *
     * @param string $table table
     */
    public function createTable( $table )
    {
        $idfield = $this->getIDfield($table, true);
        $table = $this->safeTable($table);
        $sql = "
                     CREATE TABLE $table (
            $idfield BINARY(16),
                     PRIMARY KEY ( $idfield )
                     ) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ";
        $this->adapter->exec( $sql );
    }



    /**
     * Inserts a record into the database using a series of insert columns
     * and corresponding insertvalues. Returns the insert id.
     *
     * @param string $table			  table to perform query on
     * @param array  $insertcolumns columns to be inserted
     * @param array  $insertvalues  values to be inserted
     *
     * @return integer $insertid	  insert id from driver, new record id
     */
    public function insertRecord( $table, $insertcolumns, $insertvalues )
    {
        $uuids = array();
        $default = $this->defaultValue;
        $idfield = $this->getIDField($table);
        $insertcolumns[] = $idfield;
        foreach ($insertvalues as $k => $insertvalue)
        {
            $last_uid = $this->newBinaryUUID();
            $uuids[] = $last_uid;
            $insertvalues[$k][] = $last_uid;
        }
        $suffix = $this->getInsertSuffix($table);
        $table = $this->safeTable($table);
        if (count($insertvalues)>0 && is_array($insertvalues[0]) && count($insertvalues[0])>0)
        {
            foreach($insertcolumns as $k=>$v)
            {
                $insertcolumns[$k] = $this->safeColumn($v);
            }
            $insertSQL = "INSERT INTO $table ( ".implode(",",$insertcolumns)." ) VALUES ";
            $insertSQL .= "( ". implode(",",array_fill(0,count($insertcolumns)," ? "))." ) $suffix";
            $first=true;
            $ids = array();
            foreach($insertvalues as $i=>$insertvalue)
            {
                $this->adapter->getCell( $insertSQL, $insertvalue, $i );
            }
            $ids = $uuids;
            $result = count($ids)===1 ? array_pop($ids) : $ids;
        }
        else
        {
            $this->adapter->getCell( "INSERT INTO $table ($idfield) VALUES(?) $suffix", array($this->newBinaryUUID()));
            $result = $last_uid;
        }
        if ($suffix)
        {
            return $result;
        }
        return ($this->adapter->getErrorMsg()=="" ?  $last_uid : 0);
    }

    /**
     * Update a record using a series of update values.
     *
     * @param string  $table		  table
     * @param array   $updatevalues update values
     * @param binary $id			  primary key for record
     */
    public function updateRecord( $table, $updatevalues, $id)
    {
        $idfield = $this->getIDField($table, true);
        $table = $this->safeTable($table);
        $sql = "UPDATE $table SET ";
        $p = $v = array();
        foreach($updatevalues as $uv)
        {
            $p[] = " {$this->safeColumn($uv["property"])} = ? ";

            $v[]=$uv["value"];
        }
        $sql .= implode(",", $p ) ." WHERE $idfield = ?";
        $v[] = $id;
        $this->adapter->exec( $sql, $v );
    }

    /**
     * Adds a column of a given type to a table.
     *
     * @param string  $table  name of the table
     * @param string  $column name of the column
     * @param integer $type   type
     *
     */
    public function addColumn( $table, $column, $type )
    {
        $table = $this->safeTable($table);
        $type = $this->getFieldType($type);
        if(substr($column, -3) == '_id')// assume column is for uuids, dirty hack :-)

        {
            $type = 'BINARY(16)';
        }
        $column = $this->safeColumn($column);
        $sql = "ALTER TABLE $table ADD $column $type ";
        $this->adapter->exec( $sql );
    }

    /**
     * Deletes a record based on a table, column, value and operator
     *
     * @param string  $table  table
     * @param integer $value  primary key id
     *
     * @todo validate arguments for security
     */
    public function deleteRecord($table, $value)
    {
        $column = $this->getIDField($table, true);
        $table = $this->safeTable($table);
        $this->adapter->exec("DELETE FROM $table WHERE $column = ? ",array($value));
    }

    public function newBinaryUUID()
    {
        return pack("h*", str_replace('-', '', UUID::mint(4)));
    }

    /**
     * Returns an array containing the column names of the specified table.
     *
     * @param string $table table
     *
     * @return array $columns columns
     */
    public function getColumns( $table )
    {
        $table = $this->safeTable($table);
        $columnsRaw = $this->adapter->get("DESCRIBE $table");
        foreach($columnsRaw as $r)
        {
            $columns[$r["Field"]]=$r["Type"];
        }
        return $columns;
    }

    /**
     * Returns the MySQL Column Type Code (integer) that corresponds
     * to the given value type.
     *
     * @param string $value value
     *
     * @return integer $type type
     */
    public function scanType( $value )
    {

        if (is_null($value))
        {
            return self::C_DATATYPE_UINT8;
        }
        $orig = $value;
        $value = strval($value);
        if ($value=="1" || $value=="" || $value=="0")
        {
            return self::C_DATATYPE_UINT8;
        }
        if (is_numeric($value) && (floor($value)==$value) && $value >= 0 && $value <= 255 )
        {
            return self::C_DATATYPE_UINT8;
        }
        if (is_numeric($value) && (floor($value)==$value) && $value >= 0  && $value <= 4294967295 )
        {
            return self::C_DATATYPE_UINT32;
        }
        if (is_numeric($value))
        {
            return self::C_DATATYPE_DOUBLE;
        }
        if (strlen($value) <= 255)
        {
            return self::C_DATATYPE_TEXT8;
        }
        return self::C_DATATYPE_TEXT16;
    }

    /**
     * Returns the Type Code for a Column Description.
     *
     * @param string $typedescription description
     *
     * @return integer $typecode code
     */
    public function code( $typedescription )
    {
        return ((isset($this->sqltype_typeno[$typedescription])) ? $this->sqltype_typeno[$typedescription] : self::C_DATATYPE_SPECIFIED);
    }

    /**
     * Change (Widen) the column to the give type.
     *
     * @param string $table table
     * @param string $column column
     *
     * @param integer $type
     */
    public function widenColumn( $table, $column, $type )
    {
        $table = $this->safeTable($table);
        $column = $this->safeColumn($column);
        $newtype = $this->getFieldType($type);
        $changecolumnSQL = "ALTER TABLE $table CHANGE $column $column $newtype ";
        $this->adapter->exec( $changecolumnSQL );
    }

    /**
     * Adds a Unique index constrain to the table.
     *
     * @param string $table table
     * @param string $col1  column
     * @param string $col2  column
     *
     * @return void
     */
    public function addUniqueIndex( $table,$columns )
    {
        $table = $this->safeTable($table);
        sort($columns); //else we get multiple indexes due to order-effects
        foreach($columns as $k=>$v)
        {
            $columns[$k]= $this->safeColumn($v);
        }
        $r = $this->adapter->get("SHOW INDEX FROM $table");
        $name = "UQ_".sha1(implode(',',$columns));
        if ($r)
        {
            foreach($r as $i)
            {
                if ($i["Key_name"]== $name)
                {
                    return;
                }
            }
        }
        $sql = "ALTER IGNORE TABLE $table
                ADD UNIQUE INDEX $name (".implode(",",$columns).")";
        $this->adapter->exec($sql);
    }

    /**
     * Tests whether a given SQL state is in the list of states.
     *
     * @param string $state code
     * @param array  $list  array of sql states
     *
     * @return boolean $yesno occurs in list
     */
    public function sqlStateIn($state, $list)
    {
        $sqlState = "0";
        if ($state == "42S02") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_TABLE;
        if ($state == "42S22") $sqlState = RedBean_QueryWriter::C_SQLSTATE_NO_SUCH_COLUMN;
        if ($state == "23000") $sqlState = RedBean_QueryWriter::C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION;
        return in_array($sqlState, $list);
    }
}

/*
   DrUUID RFC4122 library for PHP5
    by J. King (http://jkingweb.ca/)
   Licensed under MIT license

   See http://jkingweb.ca/code/php/lib.uuid/
    for documentation

   Last revised 2010-02-15
*/

/*
Copyright (c) 2009 J. King

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
class UUID
{
    const MD5  = 3;
    const SHA1 = 5;
    const clearVer = 15;  // 00001111  Clears all bits of version byte with AND
    const clearVar = 63;  // 00111111  Clears all relevant bits of variant byte with AND
    const varRes   = 224; // 11100000  Variant reserved for future use
    const varMS    = 192; // 11000000  Microsft GUID variant
    const varRFC   = 128; // 10000000  The RFC 4122 variant (this variant)
    const varNCS   = 0;   // 00000000  The NCS compatibility variant
    const version1 = 16;  // 00010000
    const version3 = 48;  // 00110000
    const version4 = 64;  // 01000000
    const version5 = 80;  // 01010000
    const interval = 0x01b21dd213814000; // Time (in 100ns steps) between the start of the UTC and Unix epochs
    const nsDNS  = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    const nsURL  = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    const nsOID  = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    const nsX500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
    protected static $randomFunc = 'randomTwister';
    protected static $randomSource = NULL;
    //instance properties
    protected $bytes;
    protected $hex;
    protected $string;
    protected $urn;
    protected $version;
    protected $variant;
    protected $node;
    protected $time;

    public static function mint($ver = 1, $node = NULL, $ns = NULL)
    {
        /* Create a new UUID based on provided data. */
        switch((int) $ver)
        {
            case 1:
                return new self(self::mintTime($node));
            case 2:
            // Version 2 is not supported
                throw new UUIDException("Version 2 is unsupported.");
            case 3:
                return new self(self::mintName(self::MD5, $node, $ns));
            case 4:
                return new self(self::mintRand());
            case 5:
                return new self(self::mintName(self::SHA1, $node, $ns));
            default:
                throw new UUIDException("Selected version is invalid or unsupported.");
        }
    }

    public static function import($uuid)
    {
        /* Import an existing UUID. */
        return new self(self::makeBin($uuid, 16));
    }

    public static function compare($a, $b)
    {
        /* Compares the binary representations of two UUIDs.
     The comparison will return true if they are bit-exact,
      or if neither is valid. */
        if (self::makeBin($a, 16)==self::makeBin($b, 16))
            return TRUE;
        else
            return FALSE;
    }

    public function __toString()
    {
        return $this->string;
    }

    public function __get($var)
    {
        switch($var)
        {
            case "bytes":
                return $this->bytes;
            case "hex":
                return bin2hex($this->bytes);
            case "string":
                return $this->__toString();
            case "urn":
                return "urn:uuid:".$this->__toString();
            case "version":
                return ord($this->bytes[6]) >> 4;
            case "variant":
                $byte = ord($this->bytes[8]);
                if ($byte >= self::varRes)
                    return 3;
                if ($byte >= self::varMS)
                    return 2;
                if ($byte >= self::varRFC)
                    return 1;
                else
                    return 0;
            case "node":
                if (ord($this->bytes[6])>>4==1)
                    return bin2hex(substr($this->bytes,10));
                else
                    return NULL;
            case "time":
                if (ord($this->bytes[6])>>4==1)
                {
                    // Restore contiguous big-endian byte order
                    $time = bin2hex($this->bytes[6].$this->bytes[7].$this->bytes[4].$this->bytes[5].$this->bytes[0].$this->bytes[1].$this->bytes[2].$this->bytes[3]);
                    // Clear version flag
                    $time[0] = "0";
                    // Do some reverse arithmetic to get a Unix timestamp
                    $time = (hexdec($time) - self::interval) / 10000000;
                    return $time;
                }
                else
                    return NULL;
            default:
                return NULL;
        }
    }

    protected function __construct($uuid)
    {
        if (strlen($uuid) != 16)
            throw new UUIDException("Input must be a 128-bit integer.");
        $this->bytes  = $uuid;
        // Optimize the most common use
        $this->string =
            bin2hex(substr($uuid,0,4))."-".
            bin2hex(substr($uuid,4,2))."-".
            bin2hex(substr($uuid,6,2))."-".
            bin2hex(substr($uuid,8,2))."-".
            bin2hex(substr($uuid,10,6));
    }

    protected static function mintTime($node = NULL)
    {
        /* Generates a Version 1 UUID.
     These are derived from the time at which they were generated. */
        // Get time since Gregorian calendar reform in 100ns intervals
        // This is exceedingly difficult because of PHP's (and pack()'s)
        //  integer size limits.
        // Note that this will never be more accurate than to the microsecond.
        $time = microtime(1) * 10000000 + self::interval;
        // Convert to a string representation
        $time = sprintf("%F", $time);
        preg_match("/^\d+/", $time, $time); //strip decimal point
        // And now to a 64-bit binary representation
        $time = base_convert($time[0], 10, 16);
        $time = pack("H*", str_pad($time, 16, "0", STR_PAD_LEFT));
        // Reorder bytes to their proper locations in the UUID
        $uuid  = $time[4].$time[5].$time[6].$time[7].$time[2].$time[3].$time[0].$time[1];
        // Generate a random clock sequence
        $uuid .= self::randomBytes(2);
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
        // set version
        $uuid[6] = chr(ord($uuid[6]) & self::clearVer | self::version1);
        // Set the final 'node' parameter, a MAC address
        if ($node)
            $node = self::makeBin($node, 6);
        if (!$node)
        {
            // If no node was provided or if the node was invalid,
            //  generate a random MAC address and set the multicast bit
            $node = self::randomBytes(6);
            $node[0] = pack("C", ord($node[0]) | 1);
        }
        $uuid .= $node;
        return $uuid;
    }

    protected static function mintRand()
    {
        /* Generate a Version 4 UUID.
     These are derived soly from random numbers. */
        // generate random fields
        $uuid = self::randomBytes(16);
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
        // set version
        $uuid[6] = chr(ord($uuid[6]) & self::clearVer | self::version4);
        return $uuid;
    }

    protected static function mintName($ver, $node, $ns)
    {
        /* Generates a Version 3 or Version 5 UUID.
     These are derived from a hash of a name and its namespace, in binary form. */
        if (!$node)
            throw new UUIDException("A name-string is required for Version 3 or 5 UUIDs.");
        // if the namespace UUID isn't binary, make it so
        $ns = self::makeBin($ns, 16);
        if (!$ns)
            throw new UUIDException("A binary namespace is required for Version 3 or 5 UUIDs.");
        switch($ver)
        {
            case self::MD5:
                $version = self::version3;
                $uuid = md5($ns.$node,1);
                break;
            case self::SHA1:
                $version = self::version5;
                $uuid = substr(sha1($ns.$node,1),0, 16);
                break;
        }
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & self::clearVar | self::varRFC);
        // set version
        $uuid[6] = chr(ord($uuid[6]) & self::clearVer | $version);
        return ($uuid);
    }

    protected static function makeBin($str, $len)
    {
        /* Insure that an input string is either binary or hexadecimal.
     Returns binary representation, or false on failure. */
        if ($str instanceof self)
            return $str->bytes;
        if (strlen($str)==$len)
            return $str;
        else
            $str = preg_replace("/^urn:uuid:/is", "", $str); // strip URN scheme and namespace
        $str = preg_replace("/[^a-f0-9]/is", "", $str);  // strip non-hex characters
        if (strlen($str) != ($len * 2))
            return FALSE;
        else
            return pack("H*", $str);
    }

    public static function initRandom()
    {
        /* Look for a system-provided source of randomness, which is usually crytographically secure.
     /dev/urandom is tried first simply out of bias for Linux systems. */
        if (is_readable('/dev/urandom'))
        {
            self::$randomSource = fopen('/dev/urandom', 'rb');
            self::$randomFunc = 'randomFRead';
        }
        else if (class_exists('COM', 0))
        {
            try
            {
                self::$randomSource = new COM('CAPICOM.Utilities.1');  // See http://msdn.microsoft.com/en-us/library/aa388182(VS.85).aspx
                self::$randomFunc = 'randomCOM';
            }
            catch(Exception $e)
            {

            }
        }
        return self::$randomFunc;
    }

    public static function randomBytes($bytes)
    {
        return call_user_func(array('self', self::$randomFunc), $bytes);
    }

    protected static function randomTwister($bytes)
    {
        /* Get the specified number of random bytes, using mt_rand().
     Randomness is returned as a string of bytes. */
        $rand = "";
        for ($a = 0; $a < $bytes; $a++)
        {
            $rand .= chr(mt_rand(0, 255));
        }
        return $rand;
    }

    protected static function randomFRead($bytes)
    {
        /* Get the specified number of random bytes using a file handle
     previously opened with UUID::initRandom().
     Randomness is returned as a string of bytes. */
        return fread(self::$randomSource, $bytes);
    }

    protected static function randomCOM($bytes)
    {
        /* Get the specified number of random bytes using Windows'
     randomness source via a COM object previously created by UUID::initRandom().
     Randomness is returned as a string of bytes. */
        return base64_decode(self::$randomSource->GetRandom($bytes,0)); // straight binary mysteriously doesn't work, hence the base64
    }
}

class UUIDException extends \Exception
{
}
