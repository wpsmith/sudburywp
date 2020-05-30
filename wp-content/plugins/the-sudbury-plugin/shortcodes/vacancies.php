<?php
/**
 * A shortcode to generate all the vacancies in the boards and committees
 *
 * @author     Vincent Zhao<zhaov@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */

class Sudbury_Vacancies_List {

    function __construct() {

        add_action( 'init', array( &$this, 'init' ) );
    }


    function init() {
        add_shortcode( 'vacancies', array( &$this, 'shortcode' ) );
    }

    function shortcode()
    {
        $members = get_site_option( 'sudbury_all_board_membership' );
        $vacancies = array();

        foreach($members as $committee => $ids) {
            foreach($ids as $id => $member) {
                if ($member['Vacancy'] == 'Y') {
                    $vacancies[$committee][$id] = $member;
                }
            }
        }

//        echo '<pre>' . var_export($vacancies, true) . '</pre>';
        ob_start();
        ?>
        <div class="tablecap">
            <h4>Unfilled Board and Committee Positions</h4>
            <table cellspacing="0" style="display: table;" class="filter-table">
            <?php
            foreach($vacancies as $committee => $ids) { ?>
                <thead class="headers">
                <tr><th><?php echo esc_html($committee)?></th></tr>
                </thead>
                <tbody> <?php
                foreach($ids as $id => $member) { ?>
                    <tr>
                        <td>
                            <?php
                            if ($member['Status'] == "") {
                                echo "Member";
                            } else {
                                echo esc_html($member['Status']);
                            }
                            ?>
                        </td>
                    </tr> <?php
                } ?>
                </tbody> <?php
            } ?>
            </table>
            <div class="foot"></div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }
}

new Sudbury_Vacancies_List();
