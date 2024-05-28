<?php

namespace Elementor;

class WPDataTables_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'wpdatatables';
    }

    public function get_title() {
        return 'wpDataTables';
    }

    public function get_icon() {
        return 'wpdt-table-logo';
    }

    public function get_categories() {
        return [ 'wpdatatables-elementor' ];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'wpdatatables_section',
            [
                'label' => __( 'wpDataTable content', 'wpdatatables' ),
            ]
        );

        $this->add_control(
            'wpdt-table-id',
            [
                'label' => __( 'Select wpDataTable:', 'wpdatatables' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => self::wdt_get_all_tables(),
                'default' => self::wdt_return_first_table(),
            ]
        );

        $this->add_control(
            'wpdt-file-name',
            [
                'label' => __( 'Set name for export file:', 'wpdatatables' ),
                'label_block' => true,
                'type' => Controls_Manager::TEXT,
                'placeholder' => __( 'Insert name for export file', 'wpdatatables' ),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $tableShortcodeParams = '[wpdatatable id=' . $settings['wpdt-table-id'];
        $tableShortcodeParams .= $settings['wpdt-file-name'] != '' ? ' export_file_name=' . $settings['wpdt-file-name'] : '';
        $tableShortcodeParams .= ']';

        $tableShortcodeParams = apply_filters('wpdatatables_filter_elementor_table_shortcode', $tableShortcodeParams);

        echo $settings['wpdt-table-id'] != '' ? $tableShortcodeParams : self::wdt_create_table_notice();

    }

    protected function _content_template() {

    }

    public static function wdt_get_all_tables() {

        global $wpdb;
        $returnTables = [];

        $query = "SELECT id, title FROM {$wpdb->prefix}wpdatatables ORDER BY id ";

        $allTables = $wpdb->get_results($query, ARRAY_A);

        if ($allTables != null ) {
            foreach ($allTables as $table) {
                $returnTables[$table['id']] = $table['title'] . ' (id: ' . $table['id'] . ')';
            }
        } else {
            $returnTables = [];
        }

        return $returnTables;
    }

    public static function wdt_return_first_table() {

        $allTables = self::wdt_get_all_tables();
        if ($allTables != [] ) {
            reset($allTables);
            return key($allTables);
        } else {
            return '';
        }

    }

    public static function wdt_create_table_notice() {

        return 'Please create wpDataTable first. You can find detail instructions in our docs on this <a target="_blank" href="https://wpdatatables.com/documentation/general/features-overview/">link</a>.';
    }


}



