<?php
/**
 * SQLDUMP: sqlDump easy sql database dumping(tm) (https://github.com/CarlosCardenasNegro/sqlDump)
 * Copywright (c) San Miguel Software, Sl. (http://www.sanmiguelsoftware.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.TXT (https://www.tldrlegal.com/l/mit)
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copywright (c) San Miguel Software (http://www.sanmiguelsoftware.com)
 * @author      Carlos Cárdenas Negro 
 * @link        https://github.com/CarlosCardenasNegro/sqlDump
 * @since       0.1.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @version     0.1.0
 */
 
namespace ccn\sqldump;

use Monolog\Logger;

/**
 * Easy database dumping 'ala' phpMyAdmin
 *
 * @author Carlos Cárdenas Negro
 */ 
class sqlDump {
    
    protected $pdo;
    protected $logger;
    private $databaseName;
    
    function __construct(\PDO $pdo, \Monolog\Logger $logger = null) {
        
        $this->pdo = $pdo;
        $this->databaseName = $this->getName();

        if(isset($logger)) {

                $this->logger = $logger;

        }
        
    }

    private function getName() {
        
        $sql_query = "SELECT DATABASE()";

        try {

            $stmt = $this->pdo->query($sql_query);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC); 
            $result = $stmt->fetchAll();

            return $result[0]['DATABASE()'];
            
        } catch (\PDOException $exception) {
            
            print "Error!: " . $exception->getMessage();

            if(isset($logger)) {
            
                $this->logger->error("Error!: " . $exception->getMessage(), 
                        array(
                            'File' => $exception->getFile(),
                            'Line' => $exception->getLine(),
                            'Code' => $exception->getCode()
                            ));

            }

            return null;
         
        }
        
    }
    
    public function getTables() {
        
        $tables = [];
        $sql_query = "SHOW TABLES FROM " . $this->databaseName;

        try {

            $stmt = $this->pdo->query($sql_query);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC); 
            $result = $stmt->fetchAll();

            foreach ($result as $key => $value) {
                $tables[] = $value['Tables_in_' . $this->databaseName];
            }

            return $tables;
            
        } catch (\PDOException $exception) {
            
            print "Error!: " . $exception->getMessage();

            if(isset($logger)) {
            
                $this->logger->error("Error!: " . $exception->getMessage(), 
                        array(
                            'File' => $exception->getFile(),
                            'Line' => $exception->getLine(),
                            'Code' => $exception->getCode()
                            ));

            }

            return null;

        }
    }
    
    public function getFields($table) {
        
        $fields = [];

        $sql_query = "SHOW FIELDS FROM " .  $this->databaseName . "." . $table;

        try {

            $stmt = $this->pdo->prepare($sql_query);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            // get fields
            $result = $stmt->fetchAll();

            foreach ($result as $key => $value) {
                $fields[] = $value;
            }

            return $fields;

        } catch (\PDOException $exception) {
            
            print "Error!: " . $exception->getMessage();

            if(isset($logger)) {
            
                $this->logger->error("Error!: " . $exception->getMessage(), 
                        array(
                            'File' => $exception->getFile(),
                            'Line' => $exception->getLine(),
                            'Code' => $exception->getCode()
                            ));

            }

            return null;

        }
        
    }
    
    public function getData($table) {

        $data = [];

        $sql_query = "SELECT * FROM " . $this->databaseName . "." . $table;

        try {

            $stmt = $this->pdo->prepare($sql_query);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            // get data
            $result = $stmt->fetchAll();

            foreach ($result as $key => $value) {
                $data[] = $value;
            }

            return $data;
            
        } catch (\PDOException $exception) {
            
            print "Error!: " . $exception->getMessage();

            if(isset($logger)) {
            
                $this->logger->error("Error!: " . $exception->getMessage(), 
                        array(
                            'File' => $exception->getFile(),
                            'Line' => $exception->getLine(),
                            'Code' => $exception->getCode()
                            ));

            }

            return null;
         
        }
        
    }

    public function getIndexes($table) {

        $index = [];

        $sql_query = "SHOW INDEX FROM " . $this->databaseName . "." . $table;

        try {

            $stmt = $this->pdo->prepare($sql_query);
            $stmt->execute();
            // set the resulting array to associative
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            // get indexes
            $result = $stmt->fetchAll();

            foreach ($result as $key => $value) {
                $index[] = $value;
            }

            return $index;

        } catch (\PDOException $exception) {
            
            print "Error!: " . $exception->getMessage();

            if(isset($logger)) {
            
                $this->logger->error("Error!: " . $exception->getMessage(), 
                        array(
                            'File' => $exception->getFile(),
                            'Line' => $exception->getLine(),
                            'Code' => $exception->getCode()
                            ));

            }

            return null;
        }
    }

    public function dumpSQL($fileName = "Dump.sql") {
        
        $sqlFile = 
<<<ERP
-- --------------------------------------------------------
--
-- SQL Dump Utility
--
-- Host: {$_SERVER['HTTP_HOST']}
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `$this->databaseName`
--
CREATE DATABASE IF NOT EXISTS `$this->databaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `$this->databaseName`;

-- --------------------------------------------------------

ERP;

        foreach ($this->getTables() as $table) {

            $fieldNames = [];
            $sqlFile .=
<<<ERP

--
-- Table structure for table `$table`
--
DROP TABLE IF EXISTS `$table`;
CREATE TABLE `$table` (

ERP;

            $fields = [];

            foreach ($this->getFields($table) as $field) {

                if($field['Null'] === 'YES') {
                    $null = 'NULL';
                } else {            
                    $null = 'NOT NULL';
                }

                if($field['Key'] === 'PRI') {
                    $key = 'PRIMARY KEY';
                } else {            
                    $key = '';
                }

                $extra = strtoupper($field['Extra']);

                $fieldNames[] = $field['Field'];

                $fields[] = 
<<<ERP
`{$field['Field']}` {$field['Type']} $null $extra $key
ERP;
            }
            $sqlFile .= implode(', ', $fields);
            $fieldString = implode("`, `", $fieldNames);
            $sqlFile .=
<<<ERP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `$table`
--

INSERT INTO `$table` (`$fieldString`) VALUES 

ERP;
            $dato_s = [];
            foreach ($this->getData($table) as $key1 => $value) {
                $data = [];
                foreach ($value as $dato) {
                    $data[] = $dato;
                }
                $dato_s[] = "('" . implode("', '", $data) . "')";
            }
            $sqlFile .= implode(', ', $dato_s) . ';';

        }

        $sqlFile .= 
<<<ERP

--
-- Indexes for dumped tables
--

ERP;

        foreach ($this->getTables() as $table) {

            $index = [];

            foreach ($this->getIndexes($table) as $key => $value) {

                // Don't include the primary key it was already included
                if($value['Key_name'] != 'PRIMARY') {

                    if($value['Non_unique'] === '0') {

                        $keyname = "UNIQUE KEY `{$value['Key_name']}`";

                    } else {

                        $keyname = "KEY `{$value['Key_name']}`";

                    }

                    $index[] = 'ADD ' .  $keyname . ' (`' . $value['Column_name'] . '`)';
                }

            }

            // if there is more than only primary index
            if(count($index)) {
                $sqlFile .= 
<<<ERP

ALTER TABLE `$table`

ERP;
                $sqlFile .= implode(', ', $index) . ';';
            }
        }

        $handle = fopen($fileName, 'w');
        fwrite($handle, $sqlFile);
        fclose($handle);

        return htmlspecialchars($sqlFile);
    } 

}
