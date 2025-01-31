<?php

require_once('CF7RESTPluginLifeCycle.php');
require_once('conf.php');

/**
 * Implementation for CF7RESTPluginLifeCycle.
 * Plugin writer: put your code here. Also enter code in settings.php
 */

class CF7RESTPlugin extends CF7RESTPluginLifeCycle {


	//REST call method
	protected function httpRequest($body) {

			//read from conf.php
			$host = Conf::cf7RESThost;
			$port = Conf::cf7RESTport;
			$method = Conf::cf7RESTmethod;
			$path = Conf::cf7RESTpath;
		 
		  
 
			$ch = curl_init ($path);
			curl_setopt ($ch, CURLOPT_MUTE, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, array ('Content-Type: text/xml'));
			curl_setopt ($ch, CURLOPT_POSTFIELDS, "$body");
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec ($ch);
			curl_close ($ch);
		 
			return $output;
			}

	

    /**
     * Called by install()
     * You should: Prefix all table names with $wpdb->prefix
     * Also good: additionally use the prefix for this plugin:
     * $table_name = $wpdb->prefix . $this->prefix('MY_TABLE');
     * @return void
     */
    protected function installDatabaseTables() {
        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');
        $wpdb->query("CREATE TABLE IF NOT EXISTS $tableName (
            `submit_time` INTEGER NOT NULL,
            `form_name` VARCHAR(127),
            `field_name` VARCHAR(127),
            `field_value` LONGTEXT )");
    }


    /**
     * Called by uninstall()
     * You should: Prefix all table names with $wpdb->prefix
     * Also good: additionally use the prefix for this plugin:
     * $table_name = $wpdb->prefix . $this->prefix('MY_TABLE');
     * @return void
     */
    protected function unInstallDatabaseTables() {
        global $wpdb;
        $tables = array('SUBMITS');
        foreach ($tables as $aTable) {
            $tableName = $this->prefixTableName($aTable);
            $wpdb->query("DROP TABLE IF EXISTS $tableName");
        }
    }

    public function addActionsAndFilters() {
        // Add the Admin Config page for this plugin

        // Add Config page into the Plugins menu
        //add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Add Config page as a top-level menu item on the Admin page
        add_action('admin_menu', array(&$this, 'createAdminMenu'));

        // Hook into Contact Form 7 when a form post is made to call REST service
        add_action('wpcf7_before_send_mail', array(&$this, 'callRESTFormData'));
    }


    public function callRESTFormData($cf7) {
        global $wpdb;//db
        $time = time();//db
        $tableName = $this->prefixTableName('SUBMITS');//db
		
		$body = Conf::GeBody();//REST
		
        foreach ($cf7->posted_data as $name => $value) {
            $value = is_array($value) ? implode($value, ", ") : $value;
			
			$body = str_replace( '<'.$name.'>' ,  '<'.$name.'>'.$value , $body ); //REST

            $wpdb->query("INSERT INTO `$tableName`
            (`submit_time`, `form_name`, `field_name`, `field_value`) VALUES
            ('$time', '$cf7->title', '$name', '$value')");//db

        }
				
		$result = $this->httpRequest($body);//REST call
		
		 $wpdb->query("INSERT INTO `$tableName`
            (`submit_time`, `form_name`, `field_name`, `field_value`) VALUES
            ('$time', '$cf7->title', 'response', '$result')");//db - call result

    }

    public function createAdminMenu() {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page($pluginName . ' Plugin Settings',
                      $pluginName,
                      'administrator',
                      get_class($this),
                      array(&$this, 'whatsInTheDBPage'));

    }

    public function whatsInTheDBPage() {
        ?><h2>Form Submissions</h2><?php
        global $wpdb;
        $tableName = $this->prefixTableName('SUBMITS');
        $rows = $wpdb->get_results("select distinct `form_name` from `$tableName` order by `form_name`");
        if ($rows == null || count($rows) == 0) {
            ?>No form submissions in the database<?php
                return;
        }
        $htmlFormName = $this->prefix('form');
        $currSelection = $rows[0]->form_name;
        if (isset($_POST['form_name'])) {
            $currSelection = $_POST['form_name'];
            if (isset($_POST['delete'])) {
                $wpdb->query("delete from `$tableName` where `form_name` = '$currSelection'");
            }
        }
        ?>
        <form method="post" action="" name="<?php echo $htmlFormName ?>" id="<?php echo $htmlFormName ?>">
            <select name="form_name" id="form_name"
                    onchange="document.getElementById('<?php echo $htmlFormName ?>').submit();">
            <?php foreach ($rows as $aRow) {
                $formName = $aRow->form_name;
                $selected = ($formName == $currSelection) ? "selected" : "";
                ?>
                    <option value="<?php echo $formName ?>" <?php echo $selected ?>><?php echo $formName ?></option>
                <?php } ?>
            </select>
        </form>
        <?php

        $rows = $wpdb->get_results("select `submit_time`, `field_name`, `field_value` from `$tableName` where `form_name` = '$currSelection' order by `submit_time` desc");
        $pivot = array();
        $columns = array();
        foreach ($rows as $aRow) {
            if (!isset($pivot[$aRow->submit_time])) {
                $pivot[$aRow->submit_time] = array();
            }
            $pivot[$aRow->submit_time][$aRow->field_name] = $aRow->field_value;
            $columns[count($columns)] = $aRow->field_name;
        }
        $columns = array_unique($columns);
        $style = "style='padding:5px; border-width:1px; border-style:solid; border-color:gray;'";
        ?>
        <table style="margin-top:1em; border-width:1px; border-style:solid; border-color:gray;">
            <thead>
            <th <?php echo $style ?>>Submitted</th>
            <?php foreach ($columns as $aCol) {
                echo "<th $style>$aCol</th>";
            } ?>
            </thead>
            <tbody>
            <?php foreach ($pivot as $submitTime => $data) {
                ?>
                <tr>
                    <td <?php echo $style ?>><?php echo date('Y-m-d', $submitTime) ?></td>
                <?php
                    foreach ($columns as $aCol) {
                    $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                    echo "<td $style>$cell</td>";
                }
                ?></tr><?php

            } ?>
            </tbody>
        </table>
        <p style="margin-top:2em"></p>
        <form action="" method="post">
            <input name="form_name" type="hidden" value="<?php echo $currSelection ?>"/>
            <input name="delete" type="submit" value="Delete This Form's Records"/>
        </form>

        <?php
    }
	
	
}
