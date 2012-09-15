<?php

/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *   	\file       dev/skeletons/skeleton_page.php
*		\ingroup    mymodule othermodule1 othermodule2
*		\brief      This file is an example of a php page
*		\version    $Id: skeleton_page.php,v 1.19 2011/07/31 22:21:57 eldy Exp $
*		\author		Put author name here
*		\remarks	Put here some comments
*/


require("../main.inc.php");

require_once(DOL_DOCUMENT_ROOT."/importprices/class/importprices.class.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Get parameters
$myparam = isset($_GET["myparam"])?$_GET["myparam"]:'';

$datatoimport=isset($_GET["datatoimport"])? $_GET["datatoimport"] : (isset($_POST["datatoimport"])?$_POST["datatoimport"]:'produit_1');
$format=isset($_GET["format"])? $_GET["format"] : (isset($_POST["format"])?$_POST["format"]:'');
$filetoimport=isset($_GET["filetoimport"])? $_GET["filetoimport"] : (isset($_POST["filetoimport"])?$_POST["filetoimport"]:'');
$action=isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"])?$_POST["action"]:'');
$step=isset($_GET["step"])? $_GET["step"] : (isset($_POST["step"])?$_POST["step"]:3);
$import_name=isset($_POST["import_name"])? $_POST["import_name"] : '';
$hexa=isset($_POST["hexa"])? $_POST["hexa"] : '';
$importmodelid=isset($_POST["importmodelid"])? $_POST["importmodelid"] : '';
$excludefirstline=isset($_GET["excludefirstline"])? $_GET["excludefirstline"] : (isset($_POST["excludefirstline"])?$_POST["excludefirstline"]:0);

// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}



/*******************************************************************
 * ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if ($_GET["action"] == 'add' || $_POST["action"] == 'add')
{
	$myobject=new ImportPrices($DB);
	$myobject->prop1=$_POST["field1"];
	$myobject->prop2=$_POST["field2"];
	$result=$myobject->create($user);
	if ($result > 0)
	{
		// Creation OK
	}
	{
		// Creation KO
		$mesg=$myobject->error;
	}
}

if ($step == 3 && $datatoimport)
{
	require_once(DOL_DOCUMENT_ROOT."/lib/files.lib.php");

	if ( $_POST["sendit"] && ! empty($conf->global->MAIN_UPLOAD_DOC))
	{
		dol_mkdir($conf->import->dir_temp);
		$nowyearmonth=dol_print_date(dol_now(),'%Y%m%d%H%M%S');

		$fullpath=$conf->import->dir_temp . "/" . $nowyearmonth . '-'.$_FILES['userfile']['name'];
		if (dol_move_uploaded_file($_FILES['userfile']['tmp_name'], $fullpath,1) > 0)
		{
			dol_syslog("File ".$fullpath." was added for import");
		}
		else
		{
			$langs->load("errors");
			$mesg = $langs->trans("ErrorFailedToSaveFile");
		}
	}
}



/***************************************************
 * PAGE
*
* Put here all code to build pages
****************************************************/

llxHeader('','MyPageName','');

$form=new Form($db);

if ($step == 3 && $datatoimport)
{
print '<form name="userfile" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data" METHOD="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="max_file_size" value="'.$conf->maxfilesize.'">';

print '<table class="noborder" width="100%" cellspacing="0" cellpadding="4">';

$filetoimport='';
$var=true;

print '<tr><td colspan="6">'.$langs->trans("ChooseFileToImport",img_picto('','filenew')).'</td></tr>';

print '<tr class="liste_titre"><td colspan="6">'.$langs->trans("FileWithDataToImport").'</td></tr>';

// Input file name box
$var=false;
print '<tr '.$bc[$var].'><td colspan="6">';
print '<input type="file"   name="userfile" size="20" maxlength="80"> &nbsp; &nbsp; ';
print '<input type="submit" class="button" value="'.$langs->trans("AddFile").'" name="sendit">';
print '<input type="hidden" value="'.$step.'" name="step">';
print '<input type="hidden" value="'.$format.'" name="format">';
print '<input type="hidden" value="'.$excludefirstline.'" name="excludefirstline">';
print '<input type="hidden" value="'.$datatoimport.'" name="datatoimport">';
print "</tr>\n";

// Search available imports
$dir = $conf->import->dir_temp;
$handle=@opendir(dol_osencode($dir));
if (is_resource($handle))
{
	//print '<tr><td colspan="4">';
	//print '<table class="noborder" width="100%">';

	// Search available files to import
	$i=0;
	while (($file = readdir($handle))!==false)
	{
		// readdir return value in ISO and we want UTF8 in memory
		if (! utf8_check($file)) $file=utf8_encode($file);

		if (preg_match('/^\./',$file)) continue;

		$modulepart='import';
		$urlsource=$_SERVER["PHP_SELF"].'?step='.$step.$param.'&filetoimport='.urlencode($filetoimport);
		$relativepath=$file;
		$var=!$var;
		print '<tr '.$bc[$var].'>';
		print '<td width="16">'.img_mime($file).'</td>';
		print '<td>';
		$modulepart='import';
		//$relativepath=$filetoimport;
		print '<a href="'.DOL_URL_ROOT.'/document.php?modulepart='.$modulepart.'&file='.urlencode($relativepath).'&step=3'.$param.'" target="_blank">';
		print $file;
		print '</a>';
		print '</td>';
		// Affiche taille fichier
		print '<td align="right">'.dol_print_size(dol_filesize($dir.'/'.$file)).'</td>';
		// Affiche date fichier
		print '<td align="right">'.dol_print_date(dol_filemtime($dir.'/'.$file),'dayhour').'</td>';
		// Del button
		print '<td align="right"><a href="'.DOL_URL_ROOT.'/document.php?action=remove_file&step=3'.$param.'&modulepart='.$modulepart.'&file='.urlencode($relativepath);
		print '&urlsource='.urlencode($urlsource);
		print '">'.img_delete().'</a></td>';
		// Action button
		print '<td align="right">';
		print '<a href="'.DOL_URL_ROOT.'/importprices/fiche.php?step=4'.$param.'&filetoimport='.urlencode($relativepath).'">'.img_picto($langs->trans("NewImport"),'filenew').'</a>';
		print '</td>';
		print '</tr>';
	}
	//print '</table></td></tr>';
}

print '</table></form>';


print '</div>';

if ($mesg) print $mesg;
}

// STEP 4: Page to make matching between source file and database fields

if ($step == 4 && $datatoimport)
{


	// Create classe to use for import
	$dir = DOL_DOCUMENT_ROOT . "/includes/modules/import/";
	$file = "import_csv.modules.php";
	$classname = "ImportCsv";
	require_once($dir.$file);
	$obj = new $classname($db);

	// Load source fields in input file
	$fieldssource=array();
	$src=$conf->import->dir_temp.'/'.$filetoimport;
	$result=fopen(dol_osencode($src), "r");
	if ($result)
	{
		require_once(DOL_DOCUMENT_ROOT."/importprices/lib/lib.php");
		print '<table>';
		// create array of lines from csv
		while(($arrayrecord=fgetcsv($result,100000,";"))!==false)
		{

	    $linesarray[]=$arrayrecord;
		}
        fclose($result);
	    
	    $num=count($linesarray);
	    // update product prices in loop
	    for ($i=1; $i<$num; $i++)
	    {
	    	$ref=$linesarray[$i][0];
	    	//look for product id with ref
	    	$prod_id=getProductid($ref, $db);
	    	if($prod_id>0)
	    	{
	    		$res=updateProductprice($prod_id,$linesarray[$i], $db);
	    	}
	        else
	        {
	        	print "Error during import, no such Ref.".$ref." in the database";
	        	$r=$db->rollback();
	        	$err=-1;
	        	break;
	        } 
	    	
	    
	    	
	    }
	    
	    if($err!=-1)
	    {
	    $errarray=testUpdate($linesarray, $num, $db);
	    
	    foreach ($errarray as $key=>$value)
	    {
	    	print '<table>';
	    	if($value==0)
	    	print '<tr><td>Test for line'.$key.'passed'.$value.'</td></tr>';
	    	else 
	    	print '<tr><td>Test not passed for line'.$key.'</td></tr>';
	    	print '</table>';
	    }
	    }
	}
}

/***************************************************
 * LINKED OBJECT BLOCK
*
* Put here code to view linked object
****************************************************/
//$somethingshown=$myobject->showLinkedObjectBlock();

// End of page
$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
?>
