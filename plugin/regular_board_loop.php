<?php 

/**
 * Board post loop
 * (1) Display all thread and reply content
 *
 * @package regular_board
 */
 
if ( !defined ( 'regular_board_plugin' ) ) {
	die();
}

if ( count ( $posts ) > 0 ) {

	if ( !$this_thread && !$enable_url ) {
		$thread_urls_disabled = 1;
	} else {
		$thread_urls_disabled = 0;
	}

	echo '<div class="thread'; if ( $posts->post_comment_parent ) { echo 'child'; } echo '">';
	$posts->post_id             = absint ( $posts->post_id          );
	$posts->post_parent         = absint ( $posts->post_parent      );
	$posts->post_userid         = absint ( $posts->post_userid      );
	$posts->post_moderator      = absint ( $posts->post_moderator   );
	$posts->post_reportcount    = absint ( $posts->post_reportcount );
	$posts->post_comment_parent = absint ( $posts->post_comment_parent );
	$posts->post_name           = $posts->post_name;
	$posts->post_date           = $posts->post_date;
	$posts->post_email          = $posts->post_email;
	$posts->post_title          = $posts->post_title;
	if ( strtolower ( $posts->post_guestip ) == 'null' ) {
		$posts->post_guestip = '';
	}
	$thread_parent     = '';
	if ( !$posts->post_parent ) {
		$thread_parent = 1;
	}
	if ( !$posts->post_parent ) { 
		$reply_to = $this_thread;
	}
	if ( $posts->post_parent ) {
		$reply_to       = $posts->post_parent;
		$comment_parent = $posts->post_id;
	} else {
		$comment_parent = '';
	}
	
	if ( $posts->post_guestip ) {
		$guest_post             = 1;
	} else {
		$guest_post             = 0;
	}
	
	$raw_comment                = str_replace ( array ( '\\n', '\\r', '\\' ), array ( ' || ', ' || ', '' ), $posts->post_comment );
	$posts->post_comment        = $posts->post_comment;
	if ( $protocol == 'tags' ) {
		$posts->post_comment        = str_replace ( '?b=#', '?b=', regular_board_auto_tags ( $posts->post_comment ) );
	}
	if ( $protocol == 'boards' ) {
		$posts->post_comment        = str_replace ( '?ht=#', '?ht=', regular_board_auto_tags ( $posts->post_comment ) );
	}
	$posts->post_comment        = str_replace ( array('!heaven','!sage'), '', $posts->post_comment );
	if ( preg_match ( '#\+\+(.*)\+\+#', $posts->post_comment, $match ) ) {
		$posts->post_comment    = str_replace ( '++' . $match[1] . '++', '', $posts->post_comment );
	}
	if ( preg_match ( '#\[\[title:(.*)\]\]#', $posts->post_comment, $match ) ) {
		$posts->post_comment    = str_replace ( '[[title:' . $match[1] . ']]', '', $posts->post_comment );
	}
	if ( preg_match ( '#\[\[(.*?)\]\]#', $posts->post_comment, $match ) ) {
		$posts->post_comment    = str_replace ( '[[' . $match[1] . ']]', '', $posts->post_comment );
	}
	if ( preg_match ( '#\^(.*?)\^#', $posts->post_comment, $match ) ) {
		$posts->post_comment    = str_replace ( '^' . $match[1] . '^', '', $posts->post_comment );
	}	
	$posts->post_type           = $posts->post_type;
	if ( $protocol == 'boards' ) {
		$posts->post_board          = $posts->post_board;
		$board                      = $posts->post_board;
		if ( $this_thread ) {
			if ( !$posts->post_parent ) {
				$the_board              = $posts->post_board;
			}		
		}
	}
	$posts->post_last           = $posts->post_last;
	$posts->post_sticky         = intval ( $posts->post_sticky );
	$posts->post_locked         = intval ( $posts->post_locked );
	$posts->post_password       = $posts->post_password;
	$posts->post_public         = $posts->post_public;
	$posts->post_report         = $posts->post_report;
	$posts->post_url            = $posts->post_url;
	$this_is_protected          = '';
	if ( !$posts->post_board ) { 
		$this_is_protected      = '';
	}
	
	if ( $posts->post_parent ) {
		$threaded   = $wpdb->get_results ( $wpdb->prepare ("SELECT $regular_board_posts_select FROM $regular_board_posts WHERE post_parent = %d AND post_comment_parent = %d ORDER BY post_last ASC", $posts->post_parent, $posts->post_id  ) );
	}
	if ( $protectedboards) {
		if ( in_array ( $posts->post_board, $protectedboards, true ) ) {
			$this_is_protected = 1;
		}
	}
	if ( $posts->post_comment == '[deleted]' && $posts->post_title == '[deleted]' && strtolower ( $posts->post_name ) == 'null' ) {
		if ( $protocol == 'boards' ) {
			if ( $posts->post_board ) {
				$post_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = '$posts->post_board'" );
			}
		}	
		$wpdb->delete ( $regular_board_posts, array ( 'post_id' => $posts->post_id ), array ( '%d' ) );
		$wpdb->delete ( $regular_board_posts, array ( 'post_parent' => $posts->post_id ), array ( '%d' ) );
		if ( $protocol == 'boards' ) {
			if ( $post_count > 0 ) {
				$count = ( $post_count - 1 );
			}
			if ( $post_count == 0 ) {
				$count = 0;
			}
			$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = $count WHERE board_shortname = '$posts->post_board'" );
		}
	}	
	
	if ( $posts->post_parent == 0 && !$this_is_protected ) {
		if ( $board_wipe_every && $board_wipe_every != strtolower ( 'never' ) && $board_wipe_per == strtolower ( 'thread' ) ) {
			$today_is   = strtotime ( $current_timestamp );
			if ( strpos ( strtolower ( $board_wipe_every ), 'minute' ) ) {
				$uptime = intval ( $board_wipe_every ) * 60;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'hour' ) ) {
				$uptime = intval ( $board_wipe_every ) * 3600;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'day' ) ) {
				$uptime = intval ( $board_wipe_every ) * 86400;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'week' ) ) {
				$uptime = intval ( $board_wipe_every ) * 604800;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'month' ) ) {
				$uptime = intval ( $board_wipe_every ) * 2628000;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'year' ) ) {
				$uptime = intval ( $board_wipe_every ) * 31536000;
			} elseif ( strpos ( strtolower ( $board_wipe_every ), 'second' ) ) {
				$uptime = intval ( $board_wipe_every ) * 1;
			} else {
				$uptime = intval ( $board_wipe_every ) * 60;
			}
			$board_wipe_date = strtotime ( $posts->post_last );
			$board_life = ( intval ( $board_wipe_date ) + intval ( $uptime ) );
			$next_wipe = ( intval ( $uptime ) - ( intval ( $today_is ) - intval ( $board_wipe_date ) ) );
			$wipe = number_format ( intval ( $today_is ) - intval ( $board_wipe_date ) ) / intval ( $uptime ) * 100;
				if ( strpos( $next_wipe, '-' ) !== true && $display_wipe && $display_wipe == 1 ) { 
				$wipe_on_this_date = date ( "M d, Y - h:i:s A T", $board_life );
				$wipe_countdown = $wipe_on_this_date;
			}

			if($today_is > $board_life){
				if ( $protocol == 'boards' ) {
					if ( $posts->post_board ) {
						$post_count = $wpdb->get_var ( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_board = '$posts->post_board'" );
					}
				}			
				$wpdb->delete ( $regular_board_posts, array ( 'post_id' => $posts->post_id ), array ( '%d' ) );
				$wpdb->delete ( $regular_board_posts, array ( 'post_parent' => $posts->post_id ), array ( '%d' ) );
				if ( $protocol == 'boards' ) {
					if ( $post_count > 0 ) {
						$count = ( $post_count - 1 );
					}
					if ( $post_count == 0 ) {
						$count = 0;
					}
					$wpdb->query ( "UPDATE $regular_board_boards SET board_postcount = $count WHERE board_shortname = '$posts->post_board'" );
				}
			}
		}
	}	
	if ( $this_area != 'gallery' ) {

	
		/**
		 * Determine (1)post-public status, (2)current-user-capabilities and 
		 * (3) whether to set view status to 1 (viewable) or 0 (non-viewable).
		 * Posts that are marked for deletion or spam can only be viewed by 
		 * the creator (in the case of deletion) or by mods (in the case of spam).
		 * All other posts are viewable by default.
		 */
		 
		$viewthis = 1;
		if ( $posts->post_public != 1 ) {
			$viewthis = 0;
		}
		if ( $is_moderator || $is_user_mod ) {
			$viewthis = 1;
		}

		if ( $viewthis ) {
		
			/**
			 * Determine last poster and disable commenting based 
			 * on this determination (if last poster userid belongs to 
			 * currently viewing user's profile.
			 */
			 
			if ( $posts->post_parent == 0 ) {
				$thread_reply_count = $posts->post_reply_count;
				if ( $thread_reply_count == 0 && $posts->post_userid == $profileid ) {
					$tlast = 1;
				} else {
					$tlast = 0;
				}
			}
			if ( $posts->post_parent != 0 ) {
				$post_nom++;
				if ( $thread_reply_count > 0 && $post_nom == $thread_reply_count ) {
					$tlast = 0;
					if ( $posts->post_userid == $profileid ) {
						$tlast = 1;
					} else {
						$tlast = 0;
					}
				}
			}
			
			if ( $posts->post_parent && $post_nom == 1 && $this_thread == $posts->post_id ) {
				echo '<p><a href="?t=' . $posts->post_parent . '"><i class="fa fa-arrow-left"> This conversation has been branched &mdash; go back to the main discussion</i></a></p>';
			}
			
			if ( $posts->post_comment_parent && $posts->post_userid == $profileid ) {
				$tlast = 1;
			}
			
			// Strip www from all instances of post_url.
			$posts->post_url   = str_replace ( array ('//www.', 'https://www.' ), array( '//', 'https://' ), $posts->post_url );
			
			// Strip \s from titles
			$posts->post_title = str_replace ( '\\', '', $posts->post_title );
			if ( $this_thread ) {
				$post_title        = $posts->post_title;
			}
			
			/**
			 * If creating a child template, begin editing below this
			 * point.
			 */

			/**
			 * Variables are grabbed from the database in the following format:
			 * $posts->field / where field is the following format: post_secondary.
			 * The following fields are available to be displayed:
			 *
			 * post_id           ID of the post 
			 * post_parent       If is a reply, parent will be ID of the thread.  Otherwise, will be 0.
			 * post_name         Name of the poster.  Anonymous if no name is set in options.
			 * post_date         Date the post was made.
			 * post_email        Can be heaven, sage, or a random number (from roll)
			 * post_title        Entered subject of the post
			 * post_comment      Entered comment 
			 * post_type         Can be image, URL, or youtube (or nothing if url is also blank)
			 * post_url          A value will only be present if type is image, URL, or youtube
			 * post_board        The board to which this post was made
			 * post_moderator    1 if mod, 2 if usermod, 0 if user or janitor
			 * post_last         Date the post was made.  If thread, date of last reply to the thread.
			 * post_sticky       1 if sticky post, 0 if not
			 * post_locked       1 if locked, 0 if not
			 * post_password     password from options (or random) this will always be wp_hashed IN the database
			 * post_userid       the user id (profile id) of the user who made this post
			 * post_public       1 if public, 2 if reported/spam, 3 if awaiting deletion
			 * post_report       report reason 
			 * post_reportcount  how many times this thread has been reported
			 *
			 * The 'update' button checks for divs with the class of thread in the omitted div.  So you'll
			 * want your divs to at least retain the thread class if you wish to retain update functionality.
			 *
			 * Moderator actions double check whether or not the user is allowed to do them in the first place, 
			 * so even if you fuck up and wind up placing mod actions in public view, it won't really matter since 
			 * these actions check capability levels to carry out their functionality.
			 *
			 * This is a fairly straight-forward mixture of PHP and HTML.  Basic conditional statements apply.
			 * When in doubt, leave it alone.  
			 */
			 
			echo '<p>';
			
			if ( $style == 'expanded' ) {
				if ( $posts->post_url && $posts->post_type == 'image') { 
					if ( $posts->post_url || $posts->post_board ) {
						echo '<small class="fa fa-file">  File: <a href="' . $posts->post_url . '">' . basename ( $posts->post_url ) . '</a>';
						echo ' / <a href="//' . regular_board_get_domain ( $posts->post_url ) . '">' . regular_board_get_domain ( $posts->post_url ) . '</a>' ; 
						if ( $posts->post_board ) {
							echo '( <a class="load_link" href="' . $current_page . '?b=' . $posts->post_board . '">' . $posts->post_board . '</a> )';
						}
						echo '</small><br />';
					}

				}
			}			
			
			if ( $style == 'tiny' ) {
				echo '<small> ( ' . $post_no ++ . '</small> ) ';
			}
				
					echo '<strong><a class="post_title" href="';
					if ( $posts->post_url && $posts->post_type == 'URL' || $posts->post_url && $posts->post_type == 'image' ) { 
						echo esc_url ( $posts->post_url ); 
					}
					if ( $posts->post_url && $posts->post_type == 'youtube' ) { 
						echo '//youtube.com/watch?v=' . $posts->post_url; 
					}
					if ( !$posts->post_url && $posts->post_parent == 0 ) { 
						echo '?t=' . $posts->post_id; 
					}
					if ( !$posts->post_url && $posts->post_parent != 0 ) { 
						echo '?t=' . $posts->post_parent . '#' . $posts->post_id; 
					}
					if ( !$posts->post_title ) { 
						echo '">no title'; 
					} 
					if ( $posts->post_title ) { 
						echo '">' . $posts->post_title; 
					}
					echo '</a></strong> ' ;
				
					
					if ( $posts->post_comment_parent ) {
						echo '<a class="load_link" href="' . $this_page . '?t=' . $posts->post_parent . '#' . $posts->post_comment_parent . '" title="Child of #' . $posts->post_comment_parent . '"><i class="fa fa-reply"></i></a>';
					}
					
					if ( $posts->post_parent > 0 && $post_no == 3 ) {
						echo '<small><i class="fa fa-asterisk" title="First!"></i></small>';
					}					
					
					if ( !$this_is_protected ) {
						if ( $posts->post_parent == 0 ) {
							if ( $wipe_countdown ) {
								echo '<small class="prunedate" title="' . $next_wipe . ' seconds from now"><i class="fa fa-trash-o"> ' . $wipe_countdown . '</i></small> ';
							}
						}
					}
					if ( $this_is_protected ) {
						echo '<small class="prunedate" title="Protected content"><i class="fa fa-star"></i></small> ';
					}
					
					
					
					// Get the domain of the URL (if URL), return a youtube icon (if youtube), or return the board (if a post that isn't youtube or URL)
					
					if ( $style == 'tiny' ) {
						if ( $posts->post_url || $posts->post_board ) {
							echo '<small class="fa fa-globe"> ';
							
							if ( $posts->post_url && $posts->post_type != 'youtube') { 
								echo '( <a href="//' . regular_board_get_domain ( $posts->post_url ) . '">' . regular_board_get_domain ( $posts->post_url ) . '</a> )' ; 
							}
							if ( $posts->post_url && $posts->post_type == 'youtube') { 
								echo '( <a href="http://youtube.com/">youtube.com</a> )' ; 
							}
							if ( $posts->post_board ) {
								echo '( <a class="load_link" href="' . $current_page . '?b=' . $posts->post_board . '">' . $posts->post_board . '</a> )';
							}
							
							echo '</small>';
	
						}
					}
						
					// Lock / sticky status.

					if ( $posts->post_locked ) { 
						echo ' <small>locked</small> '; 
					}
					if ( $posts->post_sticky ) { 
						echo ' <small>sticky</small> '; 
					}
					
					echo '<br />';
					
					if ( $style == 'tiny' ) {
						if ( $this_area != 'media' ) {
							$media_present = 0;
							if ( $posts->post_type == 'youtube' || $posts->post_type == 'image' ) {
								$media_present = 1;
							}
							if ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//imgur.com/a/' ) !== false ) {
								$media_present = 1;
							}
							if ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//soundcloud.com/' ) !== false ) {
								$media_present = 1;
							}
							if ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//vimeo.com/' ) !== false ) {
								$media_present = 1;
							}
							if ( $posts->post_comment ) {
								$check = $posts->post_comment;
								$check = trim($check);
								$check = preg_replace( '/\s+/', ' ', $check );
								if ( preg_match ( '/\[\[title\:(.*)\]\]/', $posts->post_comment, $match ) ) {
									$check = str_replace ( '[[title:' . $match[1] . ']]', '', $check );
								}
								if ( preg_match ( '/\[\[(.*?)\]\]/', $posts->post_comment, $match ) ) {
									$check = str_replace ( '[[' . $match[1] . ']]', '', $check );
								}
								if ( preg_match ( '/\+\+(.*)\+\+/', $posts->post_comment, $match ) ) {
									$check = str_replace ( '++' . $match[1] . '++', '', $check );
								}
							}

							if ( $check ) {
								$comment_present = 1;
							}
							
							// Load button for non-thread views to load comments or media (if video or image).
							if ( !$thread_urls_disabled ) {
								if ( $media_present || $comment_present && !$posts->post_parent && !$this_thread ) { 
										echo '<i id="' . $posts->post_id . '" ';
										if (  $media_present  ) { 
											echo ' grab="media" ';
										}
										if ( $comment_present ) { 
											echo ' grab="comment" ';
										}
										echo 'class="fa fa-plus-square loadme" data="'.$current_page.'?t=';
											if ( $posts->post_parent == 0 ) { echo $posts->post_id; } 
											if ( $posts->post_parent > 0 ) { echo $posts->post_parent; }
										echo '&amp;a=media"></i><i id="' . $posts->post_id . '" class="fa fa-minus-square hideme hidden"></i>'; 
									}
								}
							}
						
					}

					
					// If moderator, display whether or not the post has been (1)reported (2)marked as spam (3)marked for deletion
					if ( $is_moderator || $is_user_mod ) {
						if ( $posts->post_report ) {
							echo ' [ <strong>++This post has been reported for ' . $posts->post_report . '.  It has been reported ' . $posts->post_reportcount . ' times.</strong> ] ';
						}
						if ( $posts->post_public == 2 ) {
							echo ' [ <strong>SPAM</strong> ] ';
						}
						if ( $posts->post_public == 3 ) {
							echo ' [ <strong>Marked for deletion</strong> ] ';
						}
					}
					
					// Meta information (poster name, post date, mod code, id, etc.)
					
					if ( $style == 'tiny' ) {
						echo ' submitted <span title="' . $posts->post_date . '">' . regular_board_timesince( $posts->post_date ) . '</span> by ';
						if ( $guest_post == 1 ) {
							echo 'guest';
						}
						if ( $guest_post == 0 ) {
							if ( strtolower ( $posts->post_name ) == 'null' ) {
								echo 'anonymous'; 
							}
							if ( strtolower ( $posts->post_name ) != 'null' ) { 
								echo '<a class="load_link" href="' . $current_page . '?u=' . $posts->post_name . '">' . $posts->post_name . '</a>'; 
							}
						}
						
						if ( $id_display ) {
							if ( $posts->post_userid ) {
								echo '<em> <strong class="user_hash">id ##: ' . $posts->post_userid . '</strong> </em>';
							}
						}
						if ( $posts->post_moderator == 1 ) {
							if ( $posts->post_userid ) {
								echo '<small>' . $mod_code . '</small> '; 
							}
						}
						if ( $posts->post_moderator == 2 ) {
							echo '<small>' . $user_mod_code . '</small> '; 
						}						
					}
					if ( $style == 'expanded' ) {
						if ( strtolower ( $posts->post_name ) == 'null' ) {
							echo 'anonymous'; 
						}				
						if ( strtolower ( $posts->post_name ) != 'null' ) { 
							echo '<a class="load_link" href="' . $current_page . '?u=' . $posts->post_name . '">' . $posts->post_name . '</a>'; 
						}
						if ( $id_display ) {
							if ( $posts->post_userid ) {
								echo '<em> <strong class="user_hash">id ##: ' . $posts->post_userid . '</strong> </em>';
							}
						}
						if ( $posts->post_moderator == 1 ) {
							if ( $posts->post_userid ) {
								echo '<small>' . $mod_code . '</small> '; 
							}
						}
						if ( $posts->post_moderator == 2 ) {
							echo '<small>' . $user_mod_code . '</small> '; 
						}					
						echo '&mdash; <span title="' . $posts->post_date . '">' . $posts->post_date . '</span>';
						echo ' &mdash; No.' . $posts->post_id;
					}
					

					if ( count ( $threaded ) > 0 ) {
						echo '<br /> ';
						echo ' replies: ';
						foreach ( $threaded as $threads ) {
							echo ' <a class="load_link" href="' . $this_page . '?t=' . $threads->post_parent . '#' . $threads->post_id . '">' . $threads->post_id . '</a> ';
						}
					}
					echo '<br />';
					
					echo '<a class="load_link" href="' . $current_page;
					if ( $posts->post_parent == 0 ) {
						echo '?t=' . $posts->post_id . '">';
						if ( $thread_reply_count == 0 ) { echo 'no comments '; }
						if ( $thread_reply_count == 1 ) { echo '1 comment '; }
						if ( $thread_reply_count > 1 ) { echo $thread_reply_count . ' comments '; }
					}
					
					if ( $posts->post_parent != 0 ) {
						echo '?t=' . $posts->post_parent . '#' . $posts->post_id . '">permalink';
					}
					
					
					echo '</a>';
					if ( $posts->post_parent ) {
						echo ' &mdash; <a href="?t=' . $posts->post_id . '"><i class="fa fa-code-fork"></i></a>';
					}
					
					if ( $style == 'tiny' ) {
						if ( !$posts->post_parent && $posts->post_url ) {
							if ( $posts->post_comment || $posts->post_type == 'youtube' || $posts->post_type == 'image' || strpos ( $posts->post_url, '//imgur.com/a/' ) !== false || strpos ( $posts->post_url, '//vimeo.com/' ) !== false || strpos ( $posts->post_url, '//soundcloud.com/' ) !== false ) {
								echo ' | <a class="load_link" href="' . $current_page . '?t=' . $posts->post_id . '&amp;a=media">load with media expanded</a>';						
							}
						}
					}
					
					if ( !$posts->post_parent ) {
						if ( $thread_reply_count > 0 ) {
							echo ' ( <span title="' . $posts->post_last . '">' . regular_board_timesince( $posts->post_last ) . '</span> ) ';
						}
					}
					if ( $style == 'tiny' ) {
						echo ' &mdash; [#' . $posts->post_id . '] <br /> ';
					}
					
					// Post actions ( edit, delete, spam, lock, sticky, ban, move, report )
					if ( $user_exists ) {
						if ( $profile_name == $posts->post_name && $profilepassword == $posts->post_password || $is_moderator || $is_user_mod || $is_user_janitor ) { 
							echo ' <a class="load_link" href="' . $current_page . '?a=editpost&amp;t=' . $posts->post_id . '">edit</a> '; 
						}
					}
				
					echo '<span class="post_action">';
					if ( $is_moderator || $user_exists ) {
							if ( $profile_name == $posts->post_name && $profilepassword == $posts->post_password || $is_moderator || $is_user_mod || $is_user_janitor ) { 
								if ( $posts->post_public == 3 ) {
									echo '<a data="' . $posts->post_id . '" href="' . $current_page . '?a=undelete&amp;t=' . $posts->post_id . '">undelete</a>
									<a data="' . $posts->post_id . '" href="' . $current_page . '?a=destroy&amp;t=' . $posts->post_id . '">permanently delete</a>';
									
								} else {					
									echo '<a data="' . $posts->post_id . '" href="' . $current_page . '?a=delete&amp;t=' . $posts->post_id . '">delete</a>';
								}

								if ( $is_moderator == 1 || $is_user_mod ) {
									if ( $posts->post_public == 666 ) {
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=approve&amp;t=' . $posts->post_id . '">approve</a> ';
									}
									if ( $posts->post_reportcount > 0 ) { 
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=dismiss&amp;t=' . $posts->post_id . '">dismiss report</a> '; 
									}	
									if ( $posts->post_public == 2 ) {
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=unspam&amp;t=' . $posts->post_id . '">unspam</a> ';
									}
									if ( $posts->post_public == 1 ) {
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=spam&amp;t=' . $posts->post_id . '">spam</a> ';
									}
									if ( !$posts->post_parent ) {
										if ( count ( $getboards ) > 1 && $protocol == 'boards' ) {
											echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=move&amp;t=' . $posts->post_id . '">move</a>';
										}
										if ( $posts->post_locked == 1 ) {
											echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=unlock&amp;t=' . $posts->post_id . '">unlock</a>';
										}
										if ( $posts->post_locked == 0 ) {
											echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=lock&amp;t=' . $posts->post_id . '">lock</a>';
										}
										if ( $posts->post_sticky == 1 ) {
											echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=unsticky&amp;t=' . $posts->post_id . '">unsticky</a>';
										} elseif ( $posts->post_sticky == 0 ) {
											echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=sticky&amp;t=' . $posts->post_id . '">sticky</a>';
										}
									}
									if ( $posts->post_moderator != 1 ) {
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=ban&amp;t=' . $posts->post_id . '">ban</a>';
									}
								}
							}
							if ( $profileid != $posts->post_userid ) { 
								echo ' <a data="' . $posts->post_id . '" href="'. $current_page . '?a=report&amp;t=' . $posts->post_id . '">report</a>'; 
							}
					}
	
					// Source button (if post has an attached comment)
					if ( $posts->post_userid ) {
						if ( $posts->post_comment ) { 
							echo ' &mdash; [ <i id="' . $posts->post_id . '" class="srcme" data="' . $current_page . '?t=';
								if ( $posts->post_parent == 0 ) {
									echo $posts->post_id; 
								}
								if ( $posts->post_parent > 0 ) { 
									echo $posts->post_parent; 
								}
							echo '"> show source</i>
							<i id="' . $posts->post_id . '" class="srchideme hidden"> hide source</i> ] ';
						}
					}
					echo '</span> ';
				echo '</p>';

				echo '<div id="load' . $posts->post_id . '" class="clear"></div>';
				echo '<div id="src' . $posts->post_id . '"></div>'; 
				
				echo '<div class="hidden" id="replyto' . $posts->post_id . '"></div>';
				
			if ( $this_area == 'media' && $this_thread || $style == 'expanded' ) {
				// Media
				if ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//imgur.com/a/' ) !== false ) { 
					// Imgur album
					echo '<div class="clear media' . $posts->post_id . '"><iframe class="imgur-album" width="100%" height="550" frameborder="0" src="//imgur.com/a/' . substr ( $posts->post_url, 19 ) . '/embed"></iframe></div>'; 
				} elseif ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//soundcloud.com/' ) !== false ) { 
					// Soundcloud
					echo '<div class="clear media' . $posts->post_id . '"><iframe width="100%" height="166" scrolling="no" frameborder="no"src="http://w.soundcloud.com/player/?url=' . esc_url ( $posts->post_url ) . '&auto_play=false&color=915f33&theme_color=00FF00"></iframe></div>'; 
				} elseif ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//vimeo.com/' ) !== false ) { 
					// Vimeo embedding
					echo '<div class="clear media' . $posts->post_id . '"><iframe src="//player.vimeo.com/video/' . substr ( $posts->post_url, 17 ) . '?title=0&amp;byline=0&amp;portrait=0&amp;color=d6cece" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>'; 
				} elseif ( $posts->post_type == 'youtube' && $posts->post_url ) { 
					// Youtube embedding
					echo '<div class="clear media' . $posts->post_id . '"><iframe width="600" height="338" src="//www.youtube.com/embed/' . $posts->post_url . '" frameborder="0" allowfullscreen></iframe></div>'; 
				} elseif ( $posts->post_type == 'image' && $posts->post_url ) { 
					// Image embedding
					echo '<div class="clear media' . $posts->post_id . '"><a href="' . $posts->post_url . '"><img class="';
						if ( $posts->post_comment ) {
							echo 'imageOP'; 
						} else {
							echo 'imageFULL';
						}
					echo '" alt="image" src="' . $posts->post_url . '"/></a></div>';
				}
			}
			
			if ( $thread_urls_disabled || $this_thread || $this_area == 'history' || $this_area == 'replies' || $this_user ) { 

				if ( $thread_urls_disabled || $posts->post_comment && $this_thread || $this_area == 'history' || $this_area == 'replies' || $this_user ) {
					// Comment

					if ( $auto_url ) {
						$urls = wp_extract_urls ( strip_tags ( sanitize_text_field ( $posts->post_comment ) ) );
						$n    = 0;
					}
					
					if ( $search_enabled && $search ) { 
						$posts->post_comment = str_replace ( $search, '<span class="searchresult">' . $search . '</span>', $posts->post_comment );
					}
					
					echo '<div class="clear comment' . $posts->post_id . '">';
					
					$posts->post_comment = str_replace ( array ( '\\n', '\\r', '\\'), array( '<br /><br />','<br /><br />','' ), $posts->post_comment );
					
					if ( $formatting ) {
						$posts->post_comment = regular_board_format ( $posts->post_comment );
					} else {
						$posts->post_comment = $posts->post_comment;
					}
					
					if ( $auto_url && $urls ) {
						echo str_replace ( $urls, '', $posts->post_comment );
					} else {
						echo $posts->post_comment;
					}

					if ( $auto_url ) {
						/**
						* wp_extract_urls to extract all urls found in the comment and return 
						* a div full of the found urls, auto-linked to their destinations.
						*/
	
						if ( $urls ) {
							echo '<div class="urls">attached urls:  ';
							$n    = 0;
							foreach ( $urls as $url ) {
								if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
										$path_info = pathinfo ( $url );
										if ( 
											$path_info['extension'] != 'jpg' ||
											$path_info['extension'] != 'gif' ||
											$path_info['extension'] != 'jpeg' ||
											$path_info['extension'] != 'png'
										) {
										$n++;
										if ( $n <= $max_links ) {
											echo '<a href="' . $url . '">' . $n . ': ' . regular_board_get_domain ( $url ) . '</a>';
										}
									}
								}
							}
							echo '</div><div class="imgs">';
							$n    = 0;
							foreach ( $urls as $url ) {
								if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
									if ( regular_board_get_domain ( $url ) == 'imgur.com' ) {
										$path_info = pathinfo ( $url );
										if ( 
											$path_info['extension'] == 'jpg' ||
											$path_info['extension'] == 'gif' ||
											$path_info['extension'] == 'jpeg' ||
											$path_info['extension'] == 'png'
										) {
											$n++;
											if ( $n <= $max_links ) {
												echo '<a href="' . $url . '"><img src=" ' . $url . '" alt="Image" class="imageOP" /></a>';
											}
										}
									}
								}
							}
							echo '</div>';
						}
					}
					
					echo '</div>';
					
				}
			}
			// Comment source
				echo '<div class="src' . $posts->post_id . ' hidden">
					<blockquote>' . $raw_comment . '</blockquote>
				</div>';



		
			$post_parent = $posts->post_parent;
			$post_id     = $posts->post_id;
			
			echo '<div>';
			if ( $this_thread ) {
				if ( count ( $threaded ) > 0 ) {
					foreach ( $threaded as $posts ) {
						if ( file_exists ( ABSPATH . '/regular_board_child/regular_board_loop.php' ) ) {
							include ( ABSPATH . '/regular_board_child/regular_board_loop.php' );
						} else {
							include ( plugin_dir_path(__FILE__) . '/regular_board_loop.php' );
						}
					}
				}		
			}
			echo '</div>';
		
		/**
		 * If creating a child template, stop editing 
		 * at this point.
		 */
		} else {
		
		}
	}
	if ( $this_area == 'gallery' ) {
		if ( filter_var( $posts->post_url, FILTER_VALIDATE_URL ) ) {
			$path_info = pathinfo ( $posts->post_url );
			if ( 
				$path_info['extension'] == 'jpg' ||
				$path_info['extension'] == 'gif' ||
				$path_info['extension'] == 'jpeg' ||
				$path_info['extension'] == 'png'
			) {
				echo '<div class="gallery">';
				echo '<span><a class="load_link" href="' . $posts->post_url . '">';
				$check_subject = strtolower ( $posts->post_title );
				if ( strpos ( $check_subject, 'nsfw' ) !== false ) {
					echo '<strong>N S F W</strong>';
				} else {
					echo '<img src=" ' . $posts->post_url . '" alt="Image" />';
				}
				echo '
				</a></span>
				<a class="load_link" href="' . $current_page . '?t=';
				if ( $posts->post_parent == 0 ) { echo $posts->post_id; }
				if ( $posts->post_parent != 0 ) { echo $posts->post_parent . '#' . $posts->post_id; }
				if ( $posts->post_title == '' ) { $posts->post_title = 'No subject'; }
				echo '">' . str_replace ( '\\', '', $posts->post_title ) . '</a> ';
				if ( $posts->post_board ) {
					echo '( <a class="load_link" href="' . $current_page . '?b=' . $posts->post_board . '">' . $posts->post_board . '</a> )';
				}
				echo '</div>';
			}
		}
	}
	echo '</div>';
} else {
	echo '<div class="thread clear"><p><strong>Nothing to see here.</strong></p></div>';
}