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

	$posts->post_id          = absint ( $posts->post_id          );
	$posts->post_parent      = absint ( $posts->post_parent      );
	$posts->post_userid      = absint ( $posts->post_userid      );
	$posts->post_moderator   = absint ( $posts->post_moderator   );
	$posts->post_reportcount = absint ( $posts->post_reportcount );
	$posts->post_name        = $posts->post_name;
	$posts->post_date        = $posts->post_date;
	$posts->post_email       = $posts->post_email;
	$posts->post_title       = $posts->post_title;
	$posts->post_comment     = $posts->post_comment;
	$posts->post_type        = $posts->post_type;
	$posts->post_board       = $posts->post_board;
	$posts->post_last        = $posts->post_last;
	$posts->post_sticky      = $posts->post_sticky;
	$posts->post_locked      = $posts->post_locked;
	$posts->post_password    = $posts->post_password;
	$posts->post_public      = $posts->post_public;
	$posts->post_report      = $posts->post_report;
	$posts->post_url         = $posts->post_url;
	
	if ( $this_area != 'gallery' ) {
		/**
		 * Determine (1)post-public status, (2)current-user-capabilities and 
		 * (3) whether to set view status to 1 (viewable) or 0 (non-viewable).
		 * Posts that are marked for deletion or spam can only be viewed by 
		 * the creator (in the case of deletion) or by mods (in the case of spam).
		 * All other posts are viewable by default.
		 */
		 
		$viewthis = 1;
			if ( $posts->post_public == 2 || $posts->post_public == 3 ) {
				$viewthis = 0;
				if ( $is_moderator || $is_user_mod ) {
					$viewthis = 1;
				}
		}

		if ( $viewthis == 1 ) {
			
			/**
			 * Determine last poster and disable commenting based 
			 * on this determination (if last poster userid belongs to 
			 * currently viewing user's profile.
			 */
			 
			if ( $posts->post_parent == 0 ) {
				$thread_reply_count = $wpdb->get_var( "SELECT COUNT(*) FROM $regular_board_posts WHERE post_parent = $posts->post_id" );
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
			
			// Strip www from all instances of post_url.
			$posts->post_url   = str_replace ( array ('//www.', 'https://www.' ), array( '//', 'https://' ), $posts->post_url );
			
			// Strip \s from titles
			$posts->post_title = str_replace ( '\\', '', $posts->post_title );
			
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
			 
			echo '<div class="thread" id="thread' . $posts->post_id . '"><p><strong><a class="post_title" href="';
					
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
					
					
					// Get the domain of the URL (if URL), return a youtube icon (if youtube), or return the board (if a post that isn't youtube or URL)
					if ( $posts->post_url ) { 
						echo '( <a href="' . regular_board_get_domain ( $posts->post_url ) . '">' . regular_board_get_domain ( $posts->post_url ) . '</a> )' ; 
					}
					if ( $posts->post_board ) {
						echo '( <a href="' . $current_page . '?b=' . $posts->post_board . '">' . $posts->post_board . '</a> )';
					}
					
					// Lock / sticky status.
					if ( $posts->post_locked ) { 
						echo ' <small>locked</small> '; 
					}
					if ( $posts->post_sticky ) { 
						echo ' <small>sticky</small> '; 
					}
					
					echo '<br />';
					
					if ( !$this_thread ) {
						// Load button for non-thread views to load comments or media (if video or image).
						if ( $posts->post_url && $posts->post_type == 'youtube' || $posts->post_url && $posts->post_type == 'image' || $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//imgur.com/a/' ) !== false || $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//vimeo.com/' ) !== false) { 
							echo '<i id="' . $posts->post_id . '" ';
							if ( $posts->post_type == 'youtube' || $posts->post_type == 'image' || $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//imgur.com/a/' ) !== false || $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//vimeo.com/' ) !== false) { 
								echo ' grab="media" ';
							}
							echo 'class="fa fa-plus-square loadme" data="'.$current_page.'?t=';
								if ( $posts->post_parent == 0 ) { echo $posts->post_id; } 
								if ( $posts->post_parent > 0 ) { echo $posts->post_parent; }
							echo '&amp;a=media"></i><i id="' . $posts->post_id . '" class="fa fa-minus-square hideme hidden"></i>'; 
						}
					}

					
					// If moderator, display whether or not the post has been (1)reported (2)marked as spam (3)marked for deletion
					if ( $is_moderator || $is_user_mod ) {
						if ( $posts->post_report ) {
							echo ' [ <strong>++This post has been reported for ' . $posts->post_report . '.  It has been reported ' . $posts->post_reportcount . ' times.</strong> ] ';
						}
						if ( $posts->post_public == 2 ) {
							echo ' [ <strong>SPAM</span> ] ';
						}
						if ( $posts->post_public == 3 ) {
							echo ' [ <strong>Marked for deletion</strong> ] ';
						}
					}
					
					// Meta information (poster name, post date, mod code, id, etc.)
					echo ' submitted ' . regular_board_timesince( $posts->post_date ) . ' by ';
					if ( !$posts->post_name || $posts->post_name == 'null' ) {
						echo 'anonymous'; 
					}					
					if ( $posts->post_name != 'null' ) { 
						echo '<a href="' . $current_page . '?u=' . $posts->post_name . '">' . $posts->post_name . '</a>'; 
					}
					
					echo '</i>';
					
					if ( $id_display ) {
						echo '<em> <strong class="user_hash">id ##: ' . $posts->post_userid . '</strong> </em>';
					}
					if ( $posts->post_moderator == 1 ) { 
						echo '<small>' . $mod_code . '</small> '; 
					}
					if ( $posts->post_moderator == 2 ) {
						echo '<small>' . $user_mod_code . '</small> '; 
					}
					echo '<br /> ';
					if ( !$this_thread ) {
						echo '<a href="' . $current_page;
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
						echo ' &mdash; [' . $posts->post_id . '] <br /> ';
					}
					
					// Post actions ( edit, delete, spam, lock, sticky, ban, move, report )
					if ( $profile_name == $posts->post_name && $profilepassword == $posts->post_password || $is_moderator || $is_user_mod || $is_user_janitor ) { 
						echo ' <a href="' . $current_page . '?a=editpost&amp;t=' . $posts->post_id . '">edit</a> '; 
					}
					
					if ( !$this_thread ) {
						if ( $tlast != 1 && $user_exists ) {
							echo '<a class="hidden noreply'.$posts->post_id.'" data="' . $posts->post_id . '">cancel</a>
							<a class="quickreply" ';
							if ( $posts->post_parent != 0 ) {
								echo ' childid="' . $posts->post_id . '" ';
							}
							echo ' data="' . $posts->post_id . '" href="' . $current_page;
							if ( $posts->post_parent == 0 ) {
								echo '?t=' . $posts->post_id;
							}
							if ( $posts->post_parent != 0 ) {
								echo '?t=' . $posts->post_parent;
							}
							echo '">quick reply</a> ';
						}
					}
					
					
					echo '<span class="post_action">';
					if ( $user_exists ) {
						if ( $profile_name == $posts->post_name && $profilepassword == $posts->post_password || $is_moderator || $is_user_mod || $is_user_janitor ) { 
							if ( $posts->post_public == 3 ) {
								echo '<a data="' . $posts->post_id . '" href="' . $current_page . '?a=undelete&amp;t=' . $posts->post_id . '">undelete</a>
								<a data="' . $posts->post_id . '" href="' . $current_page . '?a=destroy&amp;t=' . $posts->post_id . '">permanently delete</a>';
								
							} else {					
								echo '<a data="' . $posts->post_id . '" href="' . $current_page . '?a=delete&amp;t=' . $posts->post_id . '">delete</a>';
							}
							if ( $is_moderator || $is_user_mod ) {
								if ( $posts->post_reportcount > 0 ) { 
									echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=dismiss&amp;t=' . $posts->post_id . '">dismiss report</a> '; 
								}	
								if ( $posts->post_public == 2 ) {
									echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=unspam&amp;t=' . $posts->post_id . '">unspam</a> ';
								}
								if ( $posts->post_public == 1 ) {
									echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=spam&amp;t=' . $posts->post_id . '">spam</a> ';
								}
								if ( $posts->post_parent == 0 ) {
									if ( count ( $getboards ) > 1 ) {
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=move&amp;t=' . $posts->post_id . '">move</a>';
									}
									if ( $posts->post_locked == 1 ) {
										echo ' <a data="' . $posts->post_id . '" href="' . $current_page . '?a=unlock&amp;t=' . $posts->post_id . '">unlock</a>';
									} elseif ( $posts->post_locked == 0 ) {
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
					
					echo '</span> 
				</p>';

				echo '<div id="load' . $posts->post_id . '"></div>';
			
				if ( $posts->post_url ) {
					echo '<div id="load' . $posts->post_id . '"></div>';
				}
				
				if ( $posts->post_comment ) { 
					echo '<div id="src' . $posts->post_id . '"></div>'; 
				}
				
				echo '<div class="hidden" id="replyto' . $posts->post_id . '"></div>';
				
			
//			if ( $this_thread ) { 
			if ( $this_area == 'media' || $this_thread ) {
				// Media
				if ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//imgur.com/a/' ) !== false ) { 
					// ( 01 ) Imgur album
					echo '<div class="media' . $posts->post_id . '"><iframe class="imgur-album" width="100%" height="550" frameborder="0" src="//imgur.com/a/' . substr ( $posts->post_url, 19 ) . '/embed"></iframe></div>'; 
				} elseif ( $posts->post_url && $posts->post_type == 'URL' && strpos ( $posts->post_url, '//vimeo.com/' ) !== false ) { 
					// ( 02 ) Vimeo embedding
					echo '<div class="media' . $posts->post_id . '"><iframe src="//player.vimeo.com/video/' . substr ( $posts->post_url, 17 ) . '?title=0&amp;byline=0&amp;portrait=0&amp;color=d6cece" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>'; 
				} elseif ( $posts->post_type == 'youtube' && $posts->post_url ) { 
					// ( 03 ) Youtube embedding
					echo '<div class="media' . $posts->post_id . '"><a class="rb_yt" data="'.$posts->post_url.'" href="//youtube.com/watch?v=' . $posts->post_url . '"><img class="regular_board_video_thumbnail_large" src="//img.youtube.com/vi/' . $posts->post_url . '/0.jpg"></a><div id="' . $posts->post_url . '"></div></div>'; 
				} elseif ( $posts->post_type == 'image' && $posts->post_url ) { 
					// ( 04 ) Image embedding
					echo '<div class="media' . $posts->post_id . '"><a href="' . $posts->post_url . '"><img class="';
						if ( $posts->post_comment ) {
							echo 'imageOP'; 
						} else {
							echo 'imageFULL';
						}
					echo '" alt="image" src="' . $posts->post_url . '"/></a></div>';
				}
			}

				if ( $posts->post_comment ) {
					// Comment

					if ( !$this_thread ) {
						$posts->post_comment = substr ( $posts->post_comment, 0, 500 );
					}
					
					if ( $auto_url ) {
						$urls = wp_extract_urls ( strip_tags ( sanitize_text_field ( $posts->post_comment ) ) );
						$n    = 0;
					}
					
					if ( $search_enabled && $search ) { 
						$posts->post_comment = str_replace ( $search, '<span class="searchresult">' . $search . '</span>', $posts->post_comment );
					}
					
					echo '<div class="comment' . $posts->post_id . '">';
					
					$posts->post_comment = str_replace ( '\\', '', $posts->post_comment );
					
					if ( $formatting ) {
						$posts->post_comment = regular_board_format( $posts->post_comment );
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
//			}
			
			// Comment source
			if($posts->post_comment ){
				echo '<div class="src' . $posts->post_id . ' hidden">
					<textarea class="src">' . str_replace ( array ( '[', ']', '\n', '\r', '\\' ), array ( '&#91;', '&#93;', '', '', '' ), $posts->post_comment ) . '</textarea>
				</div>';
			}
			
			echo '</div>';
		
		/**
		 * If creating a child template, stop editing 
		 * at this point.
		 */
		
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
					echo '<span><a href="' . $posts->post_url . '"><img src=" ' . $posts->post_url . '" alt="Image" /></a></span>
					<a href="' . $current_page . '?t=';
					if ( $posts->post_parent == 0 ) { echo $posts->post_id; }
					if ( $posts->post_parent != 0 ) { echo $posts->post_parent . '#' . $posts->post_id; }
					if ( $posts->post_title == '' ) { $posts->post_title = 'No subject'; }
					echo '">' . str_replace ( '\\', '', $posts->post_title ) . '</a> ';
					if ( $posts->post_board ) {
						echo '( <a href="' . $current_page . '?b=' . $posts->post_board . '">' . $posts->post_board . '</a> )';
					}
					echo '</div>';
				}
			}
	}
} else {
	echo '<div class="thread"><center><em>Nothing to see here.</em></center></div>';
}