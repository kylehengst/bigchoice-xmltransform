<?php

// xml feed location
$xmlUrl = 'http://www.thebigchoice.com/feeds/full_job_xml.php';
$xmlUrl = 'feed.xml';

// sql tables
$sqlTables = 'db.sql';

// schema to translate xmldata
$xmlMapping = 'mapping.json';

// get xml data
try{
    // load schema
    if(!file_exists($xmlMapping)){
        throw new Exception('! Schema reference not found');
    }
    $xmlMap = json_decode(file_get_contents($xmlMapping), true);
    
    // load xml and convert to array
    $xmlData = json_decode(json_encode(simplexml_load_file($xmlUrl)),true);
    
    // create database
    $dbh = new PDO('sqlite:feed.db');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database\n";
    
    // create tables
    if(!file_exists($sqlTables)){
        throw new Exception('! SQL Table does not exist');
    }
    $dbh->exec(file_get_contents($sqlTables)); 
    echo "SQL Tables created\n";
    
    // translate
    foreach ($xmlMap as $xmlKey => $refValue) {
                
        if($xmlKey!='Job') continue;
        if(empty($xmlData[$xmlKey])) continue;
        
        // sql
        $columns = array_keys($refValue['_columns']);
        $columnsSQL = '`'.implode('`,`',$columns).'`';
        $valuesSQL = ':'.implode(',:',$columns);
        $query = "INSERT INTO `{$refValue['_table']}` ({$columnsSQL}) VALUES ({$valuesSQL})";
        
        // prep
        $stmt = $dbh->prepare($query);          
        
        // loop through xml data
        foreach($xmlData[$xmlKey] as $row){
            
            // prepare data
            foreach ($columns as $c) {
                
                if(empty($row[$c])) continue;
                
                $type = !empty($refValue['_columns'][$c]['type']) ? $refValue['_columns'][$c]['type'] : 'TEXT';
                $encoding = !empty($refValue['_columns'][$c]['encoding']) ? $refValue['_columns'][$c]['encoding'] : null;
                                
                switch ($type) {
                    case 'INTEGER':
                        $stmt->bindValue(":{$c}", $row[$c], PDO::PARAM_INT);
                        break;
                    default:
                        $str = $row[$c];
                        if($encoding=='Base64') $str = base64_decode($row[$c]);
                        $stmt->bindValue(":{$c}", $str, PDO::PARAM_STR);
                        break;
                }
                
            }

            // execute
            $stmt->execute();     
            
            // get id for references
            $id = $dbh->lastInsertId();
            // echo "Added ID:{$id} to {$refValue['_table']}\n";
            
            if(empty($refValue['_relationships'])) continue;
            
            // one to many
            foreach($refValue['_relationships'] as $relKey => $rel){
                
                $columns = array_values($rel['_reference']);
                $columns[] = $refValue['_primary'];
                $columnsSQL = '`'.implode('`,`',$columns).'`';
                $valuesSQL = ':'.implode(',:',$columns);
                $query = "INSERT INTO `{$rel['_table']}` ({$columnsSQL}) VALUES ({$valuesSQL})";
                $relStmt = $dbh->prepare($query);
                
                // get reference values
                $hasValues = false;
                foreach($rel['_reference'] as $refKey => $refCol){
                    if(empty($row[$relKey])) continue;
                    foreach($row[$relKey] as $values){
                        if(empty($values[$refCol])) continue;
                        $hasValues = true;
                        $relStmt->bindValue(":{$refCol}", $values[$refCol], PDO::PARAM_INT);
                    }
                }
                if(!$hasValues) continue;
                
                // primary id
                $relStmt->bindValue(":{$refValue['_primary']}", $id, PDO::PARAM_INT);
                
                $relStmt->execute();
                
                $insertId = $dbh->lastInsertId();
                
                // echo "Added Relationship ID:{$insertId} to {$rel['_table']}\n";
                
            }
            
        }
        
    }
    
    // done
    $dbh = null;
    echo "Data added\n";
    
    
} catch (PDOException $e){
    echo "{$e->getMessage()}\n";
} catch (Exception $e){
    echo "{$e->getMessage()}\n";
}