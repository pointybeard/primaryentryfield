<?php
require "vendor/autoload.php";

Class extension_primaryEntryField extends Extension{

    public function getSubscribedDelegates()
    {
        return [
            [
                'page' => '/publish/edit/',
                'delegate' => 'EntryPreEdit',
                'callback' => 'primaryEntryFieldToggleOptionsListner'
            ],
            [
                'page' => '/backend/',
                'delegate' => 'AppendPageAlert',
                'callback' => 'primaryAlreadyExistsListner'
            ],

        ];
    }

    private static function isPublishIndexPage() {
        return preg_match("@\/publish\/[^\/]+\/$@", Administration::instance()->getCurrentPageURL());
    }

    public function primaryAlreadyExistsListner($context) {
        if(!self::isPublishIndexPage()) {
            return;

        } elseif(!isset($_GET['primaryNoAutoToggleAlert'])) {
            return;
        }

        Administration::instance()->Page->pageAlert(sprintf(
            "Unable to set primary entry. Field auto toggle is disabled and another primary entry already exists."
        ), Alert::ERROR);

        return;
    }

    public function primaryEntryFieldToggleOptionsListner($context) {

        if(!self::isPublishIndexPage()) {
            return;

        } elseif(is_array($context['fields']) && array_pop(array_values($context['fields'])) != 'yes') {
            return;
        }

        $fieldElementName = array_pop(array_keys($context['fields']));
        $primaryEntryFields = $context['section']->fetchFields('primaryentry');

        if(count($primaryEntryFields) <= 0) {
            return;
        }

        foreach($primaryEntryFields as $field) {

            if($field->get("element_name") != $fieldElementName) {
                continue;
            }

            if(FieldPrimaryEntry::doesDefaultEntryAlreadyExist(
                $field->get("id"), $context['entry']->get("id")
            ) === true) {

                if($field->get("auto_toggle") == 'no') {
                    // ERROR. Kick user to an alert page
                    redirect(Administration::instance()->getCurrentPageURL() . "?" . server_safe('QUERY_STRING') . "&primaryNoAutoToggleAlert");
                }

                // Toggle all other primary entry field values for this section to 'no'
                FieldPrimaryEntry::toggleAllPrimaryFieldValuesToNo($field->get("id"));
            }

            // We found the field, so exit the loop.
            break;
        }
    }

    public function uninstall(){
        Symphony::Database()->query("DROP TABLE `tbl_fields_primaryentry`;");
    }

    public function install() {
        return (bool)Symphony::Database()->query(
            "CREATE TABLE IF NOT EXISTS `tbl_fields_primaryentry` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `field_id` int(11) unsigned NOT NULL,
              `default_state` enum('on','off') NOT NULL DEFAULT 'off',
              `auto_toggle` enum('yes','no') NOT NULL DEFAULT 'yes',
              PRIMARY KEY (`id`),
              KEY `field_id` (`field_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
        );
    }

}
