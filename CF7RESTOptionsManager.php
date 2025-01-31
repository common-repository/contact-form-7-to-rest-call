<?php

/**
 * Manage options associated with the Plugin.
 * 1. Prefix all options so they are saved in the database without name conflict
 * 2. Creates a default administration panel to setting options
 *
 * NOTE: instead of using WP add_option(), update_option() and delete_option,
 *       use $this->addOption(), updateOption(), and deleteOption() wrapper methods
 *       because they enforce added a prefix to option names in the database
 *
 * @author Nikolay Chankov, AB Solutions
 */

class CF7RESTOptionsManager {

    const optionDisplayName = '_displayName';

    /**
     * @const $optionMetaData string referencing an associative array which elements such as:
     * array(
     *   "item1" => "Item 1",          // key => display-name (simple)
     *   "item3" => array(             // key => array ( display-name, choice1, choice2, ...)
     *       "Item 3", "Excellent", "Good", "Fair", "Poor")
     */
    const optionMetaData = '_metatdata';

    public function getOptionNamePrefix() {
        return get_class($this) . '_';
    }

    /**
     * Called in activate.php
     * @param  $optionMetaData array see documentation for $this->optionMetaData
     * @return void
     */
    public function setOptionMetaData($optionMetaData) {
        $this->updateOption(self::optionMetaData, $optionMetaData);
    }

    /**
     * @return array
     */
    public function getOptionMetaData() {
        return $this->getOption(self::optionMetaData);
    }

    /**
     * @return array of string name of options
     */
    public function getOptionNames() {
        return array_keys($this->getOptionMetaData());
    }

    /**
     * Override this method to initialize options to default values and save to the database with add_option
     * @return void
     */
    protected function initOptions() {
    }

    /**
     * Cleanup: remove all options from the DB
     * @return void
     */
    protected function deleteSavedOptions() {
        $optionMetaData = $this->getOptionMetaData();
        if (is_array($optionMetaData)) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey); // how it is stored in DB
                delete_option($prefixedOptionName);
            }
        }
        $this->deleteOption(self::optionDisplayName);
        $this->deleteOption(self::optionMetaData);
    }

    /**
     * @param  $pluginDisplayName string display name of the plugin to be shown as a name/title in HTML pages
     * @return void
     */
    public function setPluginDisplayName($pluginDisplayName) {
        $this->updateOption(self::optionDisplayName, $pluginDisplayName);
    }

    /**
     * @return string display name of the plugin to show as a name/title in HTML.
     * If the display name is not set via setPluginDisplayName(), then it will just return the class name
     */
    public function getPluginDisplayName() {
        $displayName = $this->getOption(self::optionDisplayName);
        if ($displayName != null) {
            return $displayName;
        }
        return get_class($this);
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }
        return $optionNamePrefix . $name;
    }

    /**
     * Remove the prefix from the input $name.
     * Idempotent: If no prefix found, just returns what was input.
     * @param  $name string
     * @return string $optionName without the prefix.
     */
    public function unPrefix($name) {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return substr($name, strlen($optionNamePrefix));
        }
        return $name;
    }

    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return string the value from delegated call to get_option()
     */
    public function getOption($optionName) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return get_option($prefixedOptionName);
    }

    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return delete_option($prefixedOptionName);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($prefixedOptionName, $value);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value) {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($prefixedOptionName, $value);
    }

    /**
     * see: http://codex.wordpress.org/Creating_Options_Pages
     * @return void
     */
    public function createSettingsMenu() {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page($pluginName . ' Plugin Settings',
                      $pluginName,
                      'administrator',
                      get_class($this),
                      array(&$this, 'settingsPage')
        /*,plugins_url('/images/icon.png', __FILE__)*/); // if you call 'plugins_url; be sure to "require_once" it

        //call register settings function
        add_action('admin_init', array(&$aPlugin, 'registerSettings'));
    }

    public function registerSettings() {
        $settingsGroup = get_class($this) . '-settings-group';
        $optionMetaData = $this->getOptionMetaData();
        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
            register_setting($settingsGroup, $aOptionMeta);
        }
    }

    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function settingsPage() {
        $optionMetaData = $this->getOptionMetaData();

        // Save Posted Options
        if ($optionMetaData != null) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                if (isset($_POST[$aOptionKey])) {
                    $this->updateOption($aOptionKey, $_POST[$aOptionKey]);
                }
            }
        }

        // HTML for the page
        $settingsGroup = get_class($this) . '-settings-group';
        ?>
        <div class="wrap">
            <h2><?php echo $this->getPluginDisplayName(); echo _e(" History"); ?></h2>

            <form method="post" action="">
            <?php settings_fields($settingsGroup); ?>
                <table class="form-table">
                <?php
        if ($optionMetaData != null) {
                    foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                        ?>
                            <tr valign="top">
                                <th scope="row"><?php echo $aOptionKey ?></th>
                                <td>
                                <?php $this->createFormControl($aOptionKey, $aOptionMeta, $this->getOption($aOptionKey)); ?>
                                </td>
                            </tr>
                        <?php

                    }
                }
                ?>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
                </p>

            </form>
        </div>
        <?php

    }

    /**
     * Helper-function outputs the correct form element (input tag, select tag) for the given item
     * @param  $aOptionKey name of the option (unprefixed)
     * @param  $aOptionMeta meta-data for $aOptionKey
     * @param  $savedOptionValue current value for $aOptionKey
     * @return void
     */
    protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {
        if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
            $choices = array_slice($aOptionMeta, 1);
            ?>
            <p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
            <?php
                            foreach ($choices as $aChoice) {
                $selected = ($aChoice == $savedOptionValue) ? "selected" : "";
                ?>
                    <option value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $aChoice ?></option>
                <?php

            }
            ?>
            </select></p>
            <?php

        }
        else { // Simple input field
            ?>
            <p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>"
                      value="<?php echo esc_attr($savedOptionValue) ?>"/></p>
            <?php

        }

    }

}
