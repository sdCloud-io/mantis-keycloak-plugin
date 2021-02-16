<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Mantis Graph plugin
 */
class OAuthLoginPlugin extends MantisPlugin  {

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = 'Plugin title';
		$this->description = 'Plugin description';
		$this->page = 'config';

		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.0.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Plugin initialization
	 * @return void
	 */
	function init() {
		// plugin_require_api( 'core/graph_api.php' );
		// plugin_require_api( 'core/Period.php' );
		// require_api( 'summary_api.php' );
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array();
	}

	/**
	 * Plugin events
	 * @return array
	 */
	function events() {
		return array(
			'EVENT_MANTISGRAPH_SUBMENU'=> EVENT_TYPE_DEFAULT,
		);
	}

	/**
	 * plugin hooks
	 * @return array
	 */
	function hooks() {
		$t_hooks = array(
			// 'EVENT_REST_API_ROUTES' => 'routes',
			// 'EVENT_LAYOUT_RESOURCES' => 'resources',
			// 'EVENT_CORE_HEADERS' => 'csp_headers',
			// 'EVENT_MENU_SUMMARY' => 'summary_menu',
			// 'EVENT_MENU_FILTER' => 'graph_filter_menu'
		);
		return $t_hooks;
	}

	/**
	 * Add the RESTful routes that are handled by this plugin.
	 *
	 * @param string $p_event_name The event name
	 * @param array  $p_event_args The event arguments
	 * @return void
	 */
	function routes( $p_event_name, $p_event_args ) {
		// $t_app = $p_event_args['app'];
		// $t_app->group( plugin_route_group(), function() use ( $t_app ) {
		// 	$t_app->get( '/reporters', function( $req, $res, $args ) {
		// 		if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
		// 			$t_report_associative = create_reporter_summary();
		// 			$t_report = array();

		// 			foreach( $t_report_associative as $t_name => $t_count ) {
		// 				$t_report[] = array( "name" => $t_name, "count" => $t_count );
		// 			}

		// 			return $res->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_report );
		// 		}

		// 		return $res->withStatus( HTTP_STATUS_FORBIDDEN );
		// 	} );
		// } );
	}

	/**
	 * Add Content-Security-Policy directives that are needed to load scripts for CDN.
	 * @return void
	 */
	function csp_headers() {
		if( config_get_global( 'cdn_enabled' ) == ON ) {
			http_csp_add( 'script-src', self::CHARTJS_CDN );
		}
	}

	/**
	 * generate graph filter menu
	 * @return array
	 */
	function graph_filter_menu() {
		if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
			return array( '<a class="btn btn-sm btn-primary btn-white btn-round" href="' .
				plugin_page( 'issues_trend_page.php' ) . '">' . plugin_lang_get( 'issue_trends_link' ) . '</a>', );
		} else {
			return array();
		}
	}

	/**
	 * Include javascript files for chart.js
	 * @return void
	 */
	function resources() {
		// if( current( explode( '/', gpc_get_string( 'page', '' ) ) ) === $this->basename ) {
		// 	if( config_get_global( 'cdn_enabled' ) == ON ) {
		// 		$t_cdn_url = self::CHARTJS_CDN . '/npm/%s@%s/dist/';

		// 		# Chart.js library
		// 		$t_link = sprintf( $t_cdn_url, 'chart.js', self::CHARTJS_VERSION );
		// 		html_javascript_cdn_link( $t_link . 'Chart.min.js', self::CHARTJS_HASH );
		// 		html_javascript_cdn_link( $t_link . 'Chart.bundle.min.js', self::CHARTJSBUNDLE_HASH );

		// 		# Chart.js color schemes plugin
		// 		$t_link = sprintf( $t_cdn_url, 'chartjs-plugin-colorschemes', self::CHARTJS_COLORSCHEMES_VERSION );
		// 		html_javascript_cdn_link( $t_link . 'chartjs-plugin-colorschemes.min.js', self::CHARTJS_COLORSCHEMES_HASH );
		// 	} else {
		// 		$t_scripts = array(
		// 			plugin_file( 'Chart-' . self::CHARTJS_VERSION . '.min.js' ),
		// 			plugin_file( 'Chart.bundle-' . self::CHARTJS_VERSION . '.min.js' ),
		// 			plugin_file( 'chartjs-plugin-colorschemes-' . self::CHARTJS_COLORSCHEMES_VERSION . '.min.js' ),
		// 		);
		// 	}

		// 	$t_scripts[] = plugin_file( "MantisGraph.js" );
		// 	foreach( $t_scripts as $t_script ) {
		// 		echo "\t", '<script src="' . $t_script . '"></script>', "\n";
		// 	}
		// }
	}

	/**
	 * Retrieve a link to a plugin page with temporary filter parameter.
	 * @param string $p_page Plugin page name
	 * @return string
	 */
	private function get_url_with_filter( $p_page ) {
		static $s_filter_param;

		if( $s_filter_param === null ) {
			$t_filter = summary_get_filter();
			$s_filter_param = filter_get_temporary_key_param( $t_filter );
		}

		return helper_url_combine( plugin_page( $p_page ), $s_filter_param );
	}

	/**
	 * Event hook to add the plugin's tab to the Summary page menu.
	 * @return array
	 */
	function summary_menu() {
		$t_menu_items[] = '<a href="'
			. $this->get_url_with_filter( 'developer_graph.php' )
			. '">'
			. plugin_lang_get( 'tab_label' )
			. '</a>';
		return $t_menu_items;
	}

	/**
	 * Print the plugin's submenu
	 */
	function print_submenu() {
		$t_menu_items = array(
			'developer_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => lang_get( 'by_developer' ),
				'url' => $this->get_url_with_filter( 'developer_graph.php' ),
			),
			'reporter_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => lang_get( 'by_reporter' ),
				'url' => $this->get_url_with_filter( 'reporter_graph.php' ),
			),
			'status_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => plugin_lang_get( 'status_link' ),
				'url' => $this->get_url_with_filter( 'status_graph.php' ),
			),
			'resolution_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => plugin_lang_get( 'resolution_link' ),
				'url' => $this->get_url_with_filter( 'resolution_graph.php' ),
			),
			'priority_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => plugin_lang_get( 'priority_link' ),
				'url' => $this->get_url_with_filter( 'priority_graph.php' ),
			),
			'severity_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => plugin_lang_get( 'severity_link' ),
				'url' => $this->get_url_with_filter( 'severity_graph.php' ),
			),
			'category_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => plugin_lang_get( 'category_link' ),
				'url' => $this->get_url_with_filter( 'category_graph.php' ),
			),
			'issues_trend_graph.php' => array(
				'icon' => 'fa-bar-chart',
				'label' => plugin_lang_get( 'issue_trends_link' ),
				'url' => $this->get_url_with_filter( 'issues_trend_graph.php' ),
			),
		);

		# Retrieve current page
		$t_param_page = explode( '/', gpc_get_string( 'page', '' ) );
		$t_plugin_page_current = end( $t_param_page );

		print_submenu( $t_menu_items, $t_plugin_page_current, 'EVENT_MANTISGRAPH_SUBMENU' );
	}
}