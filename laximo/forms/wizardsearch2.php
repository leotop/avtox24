<?include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'wizard2'.DIRECTORY_SEPARATOR.'wizard_js.php');?>

<?echo '<h3>'.CommonExtender::LocalizeString('Search by wizard').'</h3>';?>

<div class="formExampleText">Задайте один или несколько параметров для детального подбора</div>

<div id="wizard-wrap">    

    <div class="wizardSearchWrapper">
        <?

            include('guayaquillib'.DIRECTORY_SEPARATOR.'render'.DIRECTORY_SEPARATOR.'wizard2'.DIRECTORY_SEPARATOR.'wizard.php');

            class WizardExtender extends CommonExtender
            {
                function FormatLink($type, $dataItem, $catalog, $renderer)
                {
                    if ($type == 'vehicles')
                        return 'vehicles.php?ft=findByWizard2&c='.$catalog.'&ssd='.$renderer->wizard->row['ssd'];
                    else
                        return 'wizard2.php?c='.$catalog.'&ssd=$ssd$';
                }
            }

            class GuayaquilWizard2 extends GuayaquilWizard
            {
                function DrawConditionDescription($catalog, $condition)
                {
                    return '';
                }

                function DrawVehiclesListLink($catalog, $wizard)
                {
                    return '';
                }
            }


            // Create request object
            $request = new GuayaquilRequestOEM($_GET['c'], $_GET['ssd'], Config::$catalog_data);
            if (Config::$useLoginAuthorizationMethod) {
                $request->setUserAuthorizationMethod(Config::$userLogin, Config::$userKey);
            }

            // Append commands to request
            $request->appendGetWizard2($_GET['ssd']);

            // Execute request
            $data = $request->query();

            // Check errors
            if ($request->error != '')
            {
                echo $request->error;
            }
            else
            {
            $wizard = $data[0];
            }

            $renderer = new GuayaquilWizard2(new WizardExtender());
            echo $renderer->Draw($_GET['c'], $wizard);   

        ?>
    </div>
</div>    