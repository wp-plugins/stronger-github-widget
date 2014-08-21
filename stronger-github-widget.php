<?php
/*
Plugin Name: Stronger GitHub Widget
Plugin URI: http://www.potsky.com/code/wordpress-plugins/psk-stronger-github-widget/
Description:    A plugin to display your latest GitHub events, commits, and more in a widget.
                It uses a server cache to be very light and fast in your WordPress installation.
Version: 0.4
Date: 2014-08-17
Author: Potsky
Author URI: http://www.potsky.com/about/
Licence:
    Copyright © 2013 Raphael Barbate (potsky) <potsky@me.com> [http://www.potsky.com]
    This file is part of Stronger GitHub Widget.

    Stronger GitHub Widget is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License.

    Stronger GitHub Widget is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Stronger GitHub Widget.  If not, see <http://www.gnu.org/licenses/>.
*/
if ( ( realpath( __FILE__ ) === realpath( $_SERVER[ "SCRIPT_FILENAME" ] ) )
	||
	( ! defined( 'ABSPATH' ) )
) {
	status_header( 404 );
	exit;
}

define( 'PSK_SGW_PLUGIN_FILE' , __FILE__ );
require( 'inc/define.php' );
load_plugin_textdomain( PSK_SGW_ID , false , dirname( plugin_basename( PSK_SGW_PLUGIN_FILE ) ) . '/languages/' );


/**
 * A better Github widget that displays a list of your most recent
 * active Github projects
 */
class PSK_Stronger_GitHub_Widget extends WP_Widget {

	private static $display_logo_options = array();
	private static $display_username_options = array();
	private static $types_options = array();
	private static $transient_timeout = 3600;

	function PSK_Stronger_GitHub_Widget() {

		self::$types_options = array(
			'repos'         => __( 'Repositories' , PSK_SGW_ID ) ,
			'subscriptions' => __( 'Subscriptions' , PSK_SGW_ID ) ,
			'events'        => __( 'Events' , PSK_SGW_ID ) ,
			'starred'       => __( 'Starred' , PSK_SGW_ID ) ,
			'followers'     => __( 'Followers' , PSK_SGW_ID ) ,
			'following'     => __( 'Following' , PSK_SGW_ID ) ,
		);

		self::$display_logo_options = array(
			'no'       => __( 'No logo' , PSK_SGW_ID ) ,
			'ghsmall'  => __( 'GitHub logo : small' , PSK_SGW_ID ) ,
			'ghnormal' => __( 'GitHub logo : normal' , PSK_SGW_ID ) ,
			'ghbig'    => __( 'GitHub logo : big' , PSK_SGW_ID ) ,
			'small'    => __( 'User gravatar : Small' , PSK_SGW_ID ) ,
			'normal'   => __( 'User gravatar : Normal' , PSK_SGW_ID ) ,
		);

		self::$display_username_options = array(
			'no'     => __( 'No username' , PSK_SGW_ID ) ,
			'small'  => __( 'Username only' , PSK_SGW_ID ) ,
			'normal' => __( 'Username @ GitHub' , PSK_SGW_ID ) ,
		);

		$widget_ops = array(
			'classname'   => PSK_SGW_ID ,
			'description' => __( 'Display your GitHub informations' , PSK_SGW_ID )
		);

		parent::__construct(
			PSK_SGW_ID , // Base ID
			PSK_SGW_NAME , // Name
			$widget_ops
		);

	}

	/**
	 * Get the GitHub API object
	 *
	 * It retrieves the object from a transient if existing.
	 *
	 * @param string $username   the Github username
	 * @param string $type       the Github API type
	 * @param string $widget_id  the widget instance
	 *
	 * @return object
	 */
	private static function gh_api_get( $username , $type , $widget_id ) {
		$transient = PSK_SGW_ID . $username . '_' . $type . '_' . $widget_id;
		$api_url   = ( $type == '' ) ? PSK_SGW_GITHUB_API_URL . $username : PSK_SGW_GITHUB_API_URL . $username . '/' . $type . '?sort=updated';

		if ( false === ( $result = get_transient( $transient ) ) ) {
			$gh_api = wp_remote_get( $api_url , $PSK_SGW_GITHUB_API_OPT );
			$result = json_decode( wp_remote_retrieve_body( $gh_api , $PSK_SGW_GITHUB_API_OPT ) );

			delete_transient( $transient );

			if ( ! isset( $result->message ) ) {
				set_transient( $transient , $result , self::$transient_timeout );
			}
		}
		return $result;
	}

	/**
	 * Delete a transient
	 *
	 * @param string $username   the Github username
	 * @param string $type       the Github API type
	 * @param string $widget_id  the widget instance
	 *
	 * @return object
	 */
	private static function gh_api_delete( $username , $type , $widget_id ) {
		$transient = PSK_SGW_ID . $username . '_' . $type . '_' . $widget_id;
		delete_transient( $transient );
	}

	/**
	 * Return widget header
	 *
	 * @param string $display_logo       preference
	 * @param string $username           preference
	 * @param string $display_username   preference
	 * @param string $widget_id          the widget instance
	 *
	 * @return string HTML
	 */
	private static function get_header_html( $display_logo , $username , $display_username , $widget_id ) {

		switch ( $display_username ) {
			case 'small' :
				$user = '<a href="http://github.com/' . $username . '" >' . $username . '</a>';
				break;

			case 'no' :
				$user = '';
				break;

			default:
				$user = '<a href="http://github.com/' . $username . '" >' . $username . '</a> @ GitHub';
				break;
		}

		switch ( $display_logo ) {
			case 'ghbig' :
				$r = '<img alt="GitHub Octocat" src="' . PSK_SGW_IMG_URL . 'octocat_big.png" /><br/>';
				$r .= $user;
				break;

			case 'ghsmall' :
				$r = '<table style="border:0;margin:0;padding:0;width:100%"><tr style="border:0;margin:0;padding:0" >';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:top;text-align:left;"><img alt="GitHub Octocat" src="' . PSK_SGW_IMG_URL . 'octocat_small.png" /></td>';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:middle;text-align:right;">' . $user . '</td>';
				$r .= '</tr></table>';
				break;

			case 'normal' :
				$api    = self::gh_api_get( $username , '' , $widget_id );
				$avatar = $api->avatar_url;
				$r      = '<table style="border:0;margin:0;padding:0;width:100%"><tr style="border:0;margin:0;padding:0" >';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:top;text-align:left;"><img style="width:80px;height:80px;border:1px solid #888;" alt="' . $username . '" src="' . $avatar . '" /></td>';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:middle;text-align:right;font-weight:bold;">' . $user . '</td>';
				$r .= '</tr></table>';
				break;

			case 'small' :
				$api    = self::gh_api_get( $username , '' , $widget_id );
				$avatar = $api->avatar_url;
				$r      = '<table style="border:0;margin:0;padding:0;width:100%"><tr style="border:0;margin:0;padding:0" >';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:top;text-align:left;"><img style="width:40px;height:40px;border:1px solid #888;" alt="' . $username . '" src="' . $avatar . '" /></td>';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:middle;text-align:right;font-weight:bold;">' . $user . '</td>';
				$r .= '</tr></table>';
				break;

			case 'no' :
				$r = $user;
				break;

			default :
				$r = '<table style="border:0;margin:0;padding:0;width:100%"><tr style="border:0;margin:0;padding:0" >';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:top;text-align:left;"><img alt="GitHub Octocat" src="' . PSK_SGW_IMG_URL . 'octocat_normal.png" /></td>';
				$r .= '<td style="border:0;margin:0;padding:0;vertical-align:middle;text-align:right;">' . $user . '</td>';
				$r .= '</tr></table>';
				break;
		}

		return $r;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args , $instance ) {

		extract( $args );

		$title            = empty( $instance[ 'title' ] ) ? '' : apply_filters( 'widget_title' , $instance[ 'title' ] );
		$username         = $instance[ 'username' ];
		$count            = $instance[ 'count' ];
		$types            = $instance[ 'types' ];
		$display_logo     = $instance[ 'display_logo' ];
		$display_username = $instance[ 'display_username' ];
		$show_forks       = ( $instance[ 'show_forks' ] == '1' ) ? true : false;
		$show_photo       = ( $instance[ 'show_photo' ] == '1' ) ? true : false;
		$show_hr          = ( $instance[ 'show_hr' ] == '1' ) ? true : false;
		$show_links       = ( $instance[ 'show_links' ] == '1' ) ? true : false;
		$show_date        = ( $instance[ 'show_date' ] == '1' ) ? true : false;
		$show_description = ( $instance[ 'show_description' ] == '1' ) ? true : false;


		echo $before_widget;
		echo $before_title . $title . $after_title;

		echo '<center>';
		echo self::get_header_html( $display_logo , $username , $display_username , $widget_id );
		echo '</center>';
		echo ( $show_hr ) ? '<div style="width:100%;height:3px;border-bottom:3px solid #888;opacity:0.2"></div>' : '';

		$api = self::gh_api_get( $username , $types , $widget_id );
		switch ( $types ) {

			case 'subscriptions' :
				echo self::get_gh_parser_subscriptions( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links );
				break;

			case 'events' :
				echo self::get_gh_parser_events( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links );
				break;

			case 'starred' :
				echo self::get_gh_parser_starred( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links );
				break;

			case 'followers' :
				echo self::get_gh_parser_followers( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links );
				break;

			case 'following' :
				echo self::get_gh_parser_following( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links );
				break;

			case 'repos' :
			default:
				echo self::get_gh_parser_repos( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links );
				break;
		}

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance , $old_instance ) {
		$instance                       = $old_instance;
		$instance[ 'show_forks' ]       = '';
		$instance[ 'show_photo' ]       = '';
		$instance[ 'show_links' ]       = '';
		$instance[ 'show_hr' ]          = '';
		$instance[ 'show_date' ]        = '';
		$instance[ 'show_description' ] = '';

		foreach ( $new_instance as $key => $value ) {
			$instance[ $key ] = strip_tags( $value );
		}
		return $instance;
	}

	/**
	 * Back-end widget form
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		//  Assigns values
		$instance         = wp_parse_args( (array) $instance , array(
																	'title'            => 'GitHub' ,
																	'username'         => '' ,
																	'count'            => '3' ,
																	'types'            => 'repos' ,
																	'display_logo'     => 'small' ,
																	'display_username' => 'normal' ,
																	'show_photo'       => '1' ,
																	'show_links'       => '1' ,
																	'show_hr'          => '1' ,
																	'show_date'        => '1' ,
																	'show_description' => '1' ,
																	'show_forks'       => '' ,
															   ) );
		$title            = strip_tags( $instance[ 'title' ] );
		$username         = strip_tags( $instance[ 'username' ] );
		$count            = strip_tags( $instance[ 'count' ] );
		$types            = strip_tags( $instance[ 'types' ] );
		$display_logo     = strip_tags( $instance[ 'display_logo' ] );
		$display_username = strip_tags( $instance[ 'display_username' ] );
		$show_photo       = strip_tags( $instance[ 'show_photo' ] );
		$show_links       = strip_tags( $instance[ 'show_links' ] );
		$show_forks       = strip_tags( $instance[ 'show_forks' ] );
		$show_hr          = strip_tags( $instance[ 'show_hr' ] );
		$show_date        = strip_tags( $instance[ 'show_date' ] );
		$show_description = strip_tags( $instance[ 'show_description' ] );

		if ( $username != '' ) {
			$saved = self::gh_api_get( $username , '' , $this->id_base . "-" . $this->number );
			if ( isset( $saved->message ) ) {
				echo '<div style="color:red;border:1px solid red;padding:5px;text-align:center;background:#333;">' . __( "Github error!" , PSK_SGW_ID ) . '<br/>' . $saved->message . '</div><br/>';
			} else {
				self::gh_api_delete( $username , $types , $this->id_base . "-" . $this->number );
			}
		}

		echo '<p><label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title' , PSK_SGW_ID );
		echo '<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" ';
		echo 'name="' . $this->get_field_name( 'title' ) . '" type="text" ';
		echo 'value="' . esc_attr( $title ) . '" title="' . __( 'Title of the widget as it appears on the page' , PSK_SGW_ID ) . '" />';
		echo '</label></p>';

		echo '<p><label for="' . $this->get_field_id( 'username' ) . '">' . __( 'Username' , PSK_SGW_ID );
		echo '<input class="widefat" id="' . $this->get_field_id( 'username' ) . '" ';
		echo 'name="' . $this->get_field_name( 'username' ) . '" type="text" ';
		echo 'value="' . esc_attr( $username ) . '" title="' . __( 'Your Github username' , PSK_SGW_ID ) . '"/>';
		echo '</label></p>';

		echo '<p><label for="' . $this->get_field_id( 'types' ) . '">' . __( 'What to show' , PSK_SGW_ID );
		echo '<select class="widefat" id="' . $this->get_field_id( 'types' ) . '" name="' . $this->get_field_name( 'types' ) . '">';
		foreach ( self::$types_options as $key => $value ) {
			echo '<option value="' . $key . '"' . selected( $types , $key , false ) . '>' . $value . '</option>';
		}
		echo '</select>';
		echo '</label></p>';

		echo '<p><label for="' . $this->get_field_id( 'count' ) . '">' . __( 'Number of items to show' , PSK_SGW_ID );
		echo '<input class="widefat" id="' . $this->get_field_id( 'count' ) . '" ';
		echo 'name="' . $this->get_field_name( 'count' ) . '" type="number" ';
		echo 'value="' . esc_attr( $count ) . '" title="0 for all." />';
		echo '<br><small>' . __( 'Set to 0 to display all items' , PSK_SGW_ID ) . '</small>';
		echo '</label></p>';

		echo '<p><label for="' . $this->get_field_id( 'show_forks' ) . '">' . __( 'Show forked repositories' , PSK_SGW_ID ) . ' </label>';
		echo '<input type="checkbox" name="' . $this->get_field_name( 'show_forks' ) . '" value="1" ' . checked( $show_forks , '1' , false ) . '/>';
		echo '</p>';

		echo '<p><label for="' . $this->get_field_id( 'show_photo' ) . '">' . __( 'Show users gravatar' , PSK_SGW_ID ) . ' </label>';
		echo '<input type="checkbox" name="' . $this->get_field_name( 'show_photo' ) . '" value="1" ' . checked( $show_photo , '1' , false ) . '/>';
		echo '</p>';

		echo '<p><label for="' . $this->get_field_id( 'show_hr' ) . '">' . __( 'Show separators' , PSK_SGW_ID ) . ' </label>';
		echo '<input type="checkbox" name="' . $this->get_field_name( 'show_hr' ) . '" value="1" ' . checked( $show_hr , '1' , false ) . '/>';
		echo '</p>';

		echo '<p><label for="' . $this->get_field_id( 'show_description' ) . '">' . __( 'Show description' , PSK_SGW_ID ) . ' </label>';
		echo '<input type="checkbox" name="' . $this->get_field_name( 'show_description' ) . '" value="1" ' . checked( $show_description , '1' , false ) . '/>';
		echo '</p>';

		echo '<p><label for="' . $this->get_field_id( 'show_date' ) . '">' . __( 'Show dates' , PSK_SGW_ID ) . ' </label>';
		echo '<input type="checkbox" name="' . $this->get_field_name( 'show_date' ) . '" value="1" ' . checked( $show_date , '1' , false ) . '/>';
		echo '</p>';

		echo '<p><label for="' . $this->get_field_id( 'show_links' ) . '">' . __( 'Show links' , PSK_SGW_ID ) . ' </label>';
		echo '<input type="checkbox" name="' . $this->get_field_name( 'show_links' ) . '" value="1" ' . checked( $show_links , '1' , false ) . '/>';
		echo '</p>';

		echo '<p><label for="' . $this->get_field_id( 'display_logo' ) . '">' . __( 'Display header logo' , PSK_SGW_ID );
		echo '<select class="widefat" id="' . $this->get_field_id( 'display_logo' ) . '" name="' . $this->get_field_name( 'display_logo' ) . '">';
		foreach ( self::$display_logo_options as $key => $value ) {
			echo '<option value="' . $key . '"' . selected( $display_logo , $key , false ) . '>' . $value . '</option>';
		}
		echo '</select>';
		echo '</label></p>';

		echo '<p><label for="' . $this->get_field_id( 'display_username' ) . '">' . __( 'Display header username' , PSK_SGW_ID );
		echo '<select class="widefat" id="' . $this->get_field_id( 'display_username' ) . '" name="' . $this->get_field_name( 'display_username' ) . '">';
		foreach ( self::$display_username_options as $key => $value ) {
			echo '<option value="' . $key . '"' . selected( $display_username , $key , false ) . '>' . $value . '</option>';
		}
		echo '</select>';
		echo '</label></p>';
	}

	/**
	 * Format GitHub Api : Subscriptions
	 *
	 * API call example : var_dump(json_decode(file_get_contents("https://api.github.com/users/bootstrap/subscriptions")))
	 *
	 * @param string $api                The Github API object
	 * @param string $username           preference
	 * @param string $count              preference
	 * @param string $show_date          preference
	 * @param string $show_hr            preference
	 * @param string $show_description   preference
	 * @param string $show_forks         preference
	 * @param string $show_photo         preference
	 * @param string $show_links         preference
	 *
	 * @return string HTML
	 */
	private function get_gh_parser_subscriptions( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links ) {
		$r = '<ul>';
		foreach ( $api as $item ) {
			if ( ( ! $show_forks ) && ( $item->fork ) ) continue;

			$link = $item->html_url;
			$name = ( ( $show_links ) && ( $link != '' ) ) ? '<a href="' . $link . '">' . $item->name . '</a>' : $item->name;

			$r .= '<li style="padding-top:4px;">';
			$r .= ( $show_photo ) ? '<img style="float:left;width:32px;height:32px;margin-right:5px;margin-top:5px;border:1px solid #888;" src="' . $item->owner->avatar_url . '" />' : '';
			$r .= '<strong>' . $name . '</strong>';
			$r .= ( $show_description ) ? '<br/>' . $item->description : '';
			$r .= ( $show_date ) ? '<br/><small>' . date_i18n( sprintf( '%1$s - %2$s' , get_option( 'date_format' ) , get_option( 'time_format' ) ) , strtotime( $item->updated_at ) ) . ' UTC</small>' : '';
			$r .= ( $show_hr ) ? '<div style="width:100%;height:5px;border-bottom:1px solid #888;opacity:0.2"/>' : '';
			$r .= '</li>';

			if ( --$count == 0 ) break;
		}
		$r .= '</ul>';
		return $r;
	}

	/**
	 * Format GitHub Api : Events
	 *
	 * API call example : var_dump(json_decode(file_get_contents("https://api.github.com/users/bootstrap/events")))
	 *
	 * @param string $api                The Github API object
	 * @param string $username           preference
	 * @param string $count              preference
	 * @param string $show_date          preference
	 * @param string $show_hr            preference
	 * @param string $show_description   preference
	 * @param string $show_forks         preference
	 * @param string $show_photo         preference
	 * @param string $show_links         preference
	 *
	 * @return string HTML
	 */
	private function get_gh_parser_events( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links ) {
		$r = '<ul>';
		foreach ( $api as $item ) {
			if ( ( ! $show_forks ) && ( $item->fork ) ) continue;

			switch ( $item->type ) {
				case 'FollowEvent' :
					$event = __( 'Follow' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = $item->payload->target->name;
					$img   = $item->payload->target->avatar_url;
					$link  = $item->payload->target->html_url;
					break;
				case 'CommitCommentEvent' :
					$event = __( 'Commit Comment' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->payload->comment->user->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				case 'CreateEvent' :
					$event = __( 'Create' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				case 'DeleteEvent' :
					$event = __( 'Delete' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				case 'DownloadEvent' :
					$event = __( 'Download' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = $item->payload->download->html_url;
					break;
				case 'ForkEvent' :
					$event = __( 'Fork' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = $item->payload->forkee->html_url;
					break;
				case 'ForkApplyEvent' :
					$event = __( 'Fork Apply' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				case 'GistEvent' :
					$event = __( 'Gist' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = '#' . $item->payload->gist->id;
					$img   = $item->payload->gist->user->avatar_url;
					$link  = $item->payload->gist->html_url;
					break;
				case 'GollumEvent' : // Missing //
					$event = __( 'Gollum' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = '';
					break;
				case 'IssueCommentEvent' :
					$event = __( 'Issue Comment' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->payload->comment->user->avatar_url;
					$link  = $item->payload->issue->comments_url;
					break;
				case 'IssuesEvent' :
					$event = __( 'Issues' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->payload->issue->user->avatar_url;
					$link  = $item->payload->issue->html_url;
					break;
				case 'MemberEvent' : // Missing //
					$event = __( 'Member' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = '';
					break;
				case 'PublicEvent' : // Missing //
					$event = __( 'Public' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = '';
					break;
				case 'PullRequestEvent' :
					$event = __( 'Pull Request' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->payload->pull_request->base->user->avatar_url;
					$link  = $item->payload->pull_request->html_url;
					break;
				case 'PullRequestReviewCommentEvent' :
					$event = __( 'Pull Request Review Comment' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->payload->comment->user->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				case 'PushEvent' :
					$event = __( 'Push' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				case 'TeamAddEvent' : // Missing //
					$event = __( 'Team Add' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = '';
					break;
				case 'WatchEvent' :
					$event = __( 'Watch' , PSK_SGW_ID );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
				default :
					$event = str_replace( 'Event' , '' , $item->type );
					$date  = $item->created_at;
					$desc  = ( $item->repo->name != '/' ) ? str_replace( $username . '/' , '' , $item->repo->name ) : '';
					$img   = $item->actor->avatar_url;
					$link  = PSK_SGW_GITHUB_URL . $item->repo->name;
					break;
			}

			$event = ( ( $show_links ) && ( $link != '' ) ) ? '<a href="' . $link . '">' . $event . '</a>' : $event;

			$r .= '<li style="padding-top:4px;">';
			$r .= ( $show_photo ) ? '<img style="float:left;width:32px;height:32px;margin-right:5px;margin-top:5px;border:1px solid #888;" src="' . $img . '" />' : '';
			$r .= '<strong>' . $event . '</strong>';
			$r .= ( $desc != '' ) ? '<br/>' . $desc : '';
			$r .= ( $show_date ) ? '<br/><small>' . date_i18n( sprintf( '%1$s - %2$s' , get_option( 'date_format' ) , get_option( 'time_format' ) ) , strtotime( $date ) ) . ' UTC</small>' : '';
			$r .= ( $show_hr ) ? '<div style="width:100%;height:5px;border-bottom:1px solid #888;opacity:0.2"/>' : '';
			$r .= '</li>';

			if ( --$count == 0 ) break;
		}
		$r .= '</ul>';
		return $r;
	}

	/**
	 * Format GitHub Api : Starred
	 *
	 * API call example : var_dump(json_decode(file_get_contents("https://api.github.com/users/potsky/starred")))
	 *
	 * @param string $api                The Github API object
	 * @param string $username           preference
	 * @param string $count              preference
	 * @param string $show_date          preference
	 * @param string $show_hr            preference
	 * @param string $show_description   preference
	 * @param string $show_forks         preference
	 * @param string $show_photo         preference
	 * @param string $show_links         preference
	 *
	 * @return string HTML
	 */
	private function get_gh_parser_starred( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links ) {
		$r = '<ul>';
		foreach ( $api as $item ) {
			if ( ( ! $show_forks ) && ( $item->fork ) ) continue;

			$link = $item->html_url;
			$name = ( ( $show_links ) && ( $link != '' ) ) ? '<a href="' . $link . '">' . $item->name . '</a>' : $item->name;

			$r .= '<li style="padding-top:4px;">';
			$r .= ( $show_photo ) ? '<img style="float:left;width:32px;height:32px;margin-right:5px;margin-top:5px;border:1px solid #888;" src="' . $item->owner->avatar_url . '" />' : '';
			$r .= '<strong>' . $name . '</strong>';
			$r .= ( $show_description ) ? '<br/>' . $item->description : '';
			$r .= ( $show_date ) ? '<br/><small>' . date_i18n( sprintf( '%1$s - %2$s' , get_option( 'date_format' ) , get_option( 'time_format' ) ) , strtotime( $item->updated_at ) ) . ' UTC</small>' : '';
			$r .= ( $show_hr ) ? '<div style="width:100%;height:5px;border-bottom:1px solid #888;opacity:0.2"/>' : '';
			$r .= '</li>';

			if ( --$count == 0 ) break;
		}
		$r .= '</ul>';
		return $r;
	}

	/**
	 * Format GitHub Api : Followers
	 *
	 * API call example : var_dump(json_decode(file_get_contents("https://api.github.com/users/adamsinger/followers")))
	 *
	 * @param string $api                The Github API object
	 * @param string $username           preference
	 * @param string $count              preference
	 * @param string $show_date          preference
	 * @param string $show_hr            preference
	 * @param string $show_description   preference
	 * @param string $show_forks         preference
	 * @param string $show_photo         preference
	 * @param string $show_links         preference
	 *
	 * @return string HTML
	 */
	private function get_gh_parser_followers( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links ) {
		$r = '<ul>';
		foreach ( $api as $item ) {
			if ( ( ! $show_forks ) && ( $item->fork ) ) continue;

			$link = PSK_SGW_GITHUB_URL . $item->login;
			$name = ( ( $show_links ) && ( $link != '' ) ) ? '<a href="' . $link . '">' . $item->login . '</a>' : $item->name;

			$r .= '<li style="padding-top:4px;">';
			$r .= ( $show_photo ) ? '<img style="float:left;width:32px;height:32px;margin-right:5px;margin-top:5px;border:1px solid #888;" src="' . $item->avatar_url . '" />' : '';
			$r .= '<strong>' . $name . '</strong>';
			$r .= ( $show_hr ) ? '<div style="width:100%;height:5px;border-bottom:1px solid #888;opacity:0.2"/>' : '';
			$r .= '</li>';

			if ( --$count == 0 ) break;
		}
		$r .= '</ul>';
		return $r;
	}

	/**
	 * Format GitHub Api : Repositories
	 *
	 * API call example : var_dump(json_decode(file_get_contents("https://api.github.com/users/potsky/repos")))
	 *
	 * @param string $api                The Github API object
	 * @param string $username           preference
	 * @param string $count              preference
	 * @param string $show_date          preference
	 * @param string $show_hr            preference
	 * @param string $show_description   preference
	 * @param string $show_forks         preference
	 * @param string $show_photo         preference
	 * @param string $show_links         preference
	 *
	 * @return string HTML
	 */
	private function get_gh_parser_repos( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links ) {
		$r = '<ul>';
		foreach ( $api as $item ) {
			if ( ( ! $show_forks ) && ( $item->fork ) ) continue;

			$link = $item->html_url;
			$name = ( ( $show_links ) && ( $link != '' ) ) ? '<a href="' . $link . '">' . $item->name . '</a>' : $item->name;

			$r .= '<li style="padding-top:4px;">';
			$r .= ( $show_photo ) ? '<img style="float:left;width:32px;height:32px;margin-right:5px;margin-top:5px;border:1px solid #888;" src="' . $item->owner->avatar_url . '" />' : '';
			$r .= '<strong>' . $name . '</strong>';
			$r .= ( $show_description ) ? '<br/>' . $item->description : '';
			$r .= ( $show_date ) ? '<br/><small>' . date_i18n( sprintf( '%1$s - %2$s' , get_option( 'date_format' ) , get_option( 'time_format' ) ) , strtotime( $item->updated_at ) ) . ' UTC</small>' : '';
			$r .= ( $show_hr ) ? '<div style="width:100%;height:5px;border-bottom:1px solid #888;opacity:0.2"/>' : '';
			$r .= '</li>';

			if ( --$count == 0 ) break;
		}
		$r .= '</ul>';
		return $r;
	}

	/**
	 * Format GitHub Api : Following
	 *
	 * API call example : var_dump(json_decode(file_get_contents("https://api.github.com/users/potsky/following")))
	 *
	 * @param string $api                The Github API object
	 * @param string $username           preference
	 * @param string $count              preference
	 * @param string $show_date          preference
	 * @param string $show_hr            preference
	 * @param string $show_description   preference
	 * @param string $show_forks         preference
	 * @param string $show_photo         preference
	 * @param string $show_links         preference
	 *
	 * @return string HTML
	 */
	private function get_gh_parser_following( $api , $username , $count , $show_date , $show_hr , $show_description , $show_forks , $show_photo , $show_links ) {
		$r = '<ul>';
		foreach ( $api as $item ) {
			if ( ( ! $show_forks ) && ( $item->fork ) ) continue;

			$link = PSK_SGW_GITHUB_URL . $item->login;
			$name = ( ( $show_links ) && ( $link != '' ) ) ? '<a href="' . $link . '">' . $item->login . '</a>' : $item->name;

			$r .= '<li style="padding-top:4px;">';
			$r .= ( $show_photo ) ? '<img style="float:left;width:32px;height:32px;margin-right:5px;margin-top:5px;border:1px solid #888;" src="' . $item->avatar_url . '" />' : '';
			$r .= '<strong>' . $name . '</strong>';
			$r .= ( $show_hr ) ? '<div style="width:100%;height:5px;border-bottom:1px solid #888;opacity:0.2"/>' : '';
			$r .= '</li>';

			if ( --$count == 0 ) break;
		}
		$r .= '</ul>';
		return $r;
	}


}

add_action( 'widgets_init' , create_function( '' , 'register_widget( "PSK_Stronger_GitHub_Widget" );' ) );

















