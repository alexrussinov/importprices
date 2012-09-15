This module allows you to update 5 levels prices with VAT.

After updating the result is checked line by line by comparing the data from the database with the data from the csv file.

See csv example.

Installation:

Under Dolibarr root directory:
-Get is as a submodule with command : 
 git submodule add git://github.com/alexrussinov/importprices.git htdocs/importprices

Then you need to add new menu entry for your import menu. 
If you use eldy's theme for example add in /include/menus/standard/eldy.lib.php this code:

            if (! empty($conf->import->enabled))
            {
                ...
                //New menu entry for import prices 
                $newmenu->add("/importprices/fiche.php", $langs->trans("NewImportPrices"),1,$user->rights->import->run);
            }
Don't forget to add a translation for "NewImportPrices" for your language.

The end. 
