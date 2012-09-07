<?php
/* Get product reference, pointer DB
 * Return product id for the given reference
 * Return -1 if reference not found 
 * Return -2 if query failed
 */
function getProductid($ref, $db)
{
	
	$sql="SELECT p.rowid FROM ".MAIN_DB_PREFIX."product as p WHERE p.ref='".$ref."'";
	
	$db->begin();
	$result=$db->query($sql);
	if ($result)
	{
		$obj = $db->fetch_object($result);
		if ($obj)
		{
			$id = $obj->rowid;
			return $id;		
		}
		else 
			return -1;
	}
	else return -2;
	
	$SQL = mysql_query("SELECT username FROM users WHERE username = '$new_username'");
	$result = mysql_num_rows($SQL);
}

/*
 * Get product id, line from csv, pointer on DB
 * Update product prices for given product id
 * Return 0 if OK, -1 if KO
 */
function updateProductprice($prod_id, $line, $db)
{
	$num=count($line);
	for ($i=1; $i<$num; $i++)
	{
    if(($c=$i%2)==0)
    {
    	$column='price_ttc';
    }
    else 
    {
    	$column='price';
    }
    
    $level=$i;
    //if column >=6 we calculate right value for the price level, because we have 5 price levels and 10 price columns
    if($i>=6)
    {
    	$level=$i-5;
    }
    // we have 10 columns for the prices first 5 prices coresponding column "price" and last 5 - column "price_ttc"
    if ($i<=5)
    {
	$sql="UPDATE ".MAIN_DB_PREFIX."product_price SET price=".$line[$i]." WHERE fk_product=".$prod_id." AND price_level=".$level;
	$result=$db->query($sql);
    }
    else 
    {
    	$sql="UPDATE ".MAIN_DB_PREFIX."product_price SET price_ttc=".$line[$i]." WHERE fk_product=".$prod_id." AND price_level=".$level;
    	$result=$db->query($sql);
    }
	if ($result)
		$res=0;
	else $res=-1;

	}
	$db->commit();
	return $res;
}

/*
 * function compare data from array with data from table after update for eache line
 * put 0 in to the result array if the data is equal or -1 if isn't
 * return array of results for eache line 
 */
function testUpdate($lines, $num, $db)
{
	for ($i=1; $i<=$num; $i++)
	{
		$err=0;
		$ref=$lines[$i][0];
		$id=getProductid($ref, $db);
		for ($l=1; $l<=5; $l++)
		{
			
		$sql="SELECT p.price FROM ".MAIN_DB_PREFIX."product_price as p WHERE fk_product=".$id." AND price_level=".$l;
		$result=$db->query($sql);
		  if($result)
		  {
			$obj=$db->fetch_object($result);
			$price_from_array=$lines[$i][$l];
			$price_from_database=$obj->price;
			if($price_from_array!=$price_from_database)
			{
				$err=-1;
			}
		
		  }
		}
		
		$errarray[$i]=$err;
	}
	
	return $errarray;
}