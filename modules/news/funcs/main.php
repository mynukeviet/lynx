<?php

/**
 * @Project NUKEVIET 3.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2012 VINADES.,JSC. All rights reserved
 * @Createdate 3-6-2010 0:14
 */

if( ! defined( 'NV_IS_MOD_NEWS' ) ) die( 'Stop!!!' );

$page_title = $module_info['custom_title'];
$key_words = $module_info['keywords'];

$contents = '';
$cache_file = '';

$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
$base_url_rewrite = nv_url_rewrite( $base_url, true );
$request_uri = $_SERVER['REQUEST_URI'];
if( ! ( $home OR $request_uri == $base_url_rewrite OR $request_uri == $base_url_rewrite . '/page-' . $page ) )
{
	$redirect = '<meta http-equiv="Refresh" content="3;URL=' . $base_url_rewrite . '" />';
	nv_info_die( $lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content'] . $redirect );
}
if( ! defined( 'NV_IS_MODADMIN' ) and $page < 5 )
{
	$cache_file = NV_LANG_DATA . '-' . $module_name . '-' . $module_info['template'] . '-' . $op . '-' . $page . '-' . NV_CACHE_PREFIX . '.cache';
	if( ( $cache = nv_get_cache( $cache_file ) ) != false )
	{
		$contents = $cache;
	}
}

if( empty( $contents ) )
{
	$viewcat = $module_config[$module_name]['indexfile'];
	$show_no_image = $module_config[$module_name]['show_no_image'];
	$array_catpage = array();
	$array_cat_other = array();

	if( $viewcat == 'viewcat_page_new' or $viewcat == 'viewcat_page_old' )
	{
		$order_by = ( $viewcat == 'viewcat_page_new' ) ? 'publtime DESC' : 'publtime ASC';
		
		$sdr->select( 'COUNT(*)' );
		$sdr->from( NV_PREFIXLANG . '_' . $module_data . '_rows' );
		$sdr->where( 'status= 1 AND inhome=1' );
		
		$all_page = $db->query( $sdr->get() )->fetchColumn();

		$sdr->select( 'id, catid, listcatid, topicid, admin_id, author, sourceid, addtime, edittime, publtime, title, alias, hometext, homeimgfile, homeimgalt, homeimgthumb, allowed_rating, hitstotal, hitscm, total_rating, click_rating' );
		$sdr->order( $order_by );
		$sdr->limit( $per_page, ( $page - 1 ) * $per_page);
		
		$end_publtime = 0;

		$result = $db->query( $sdr->get() );
		while( $item = $result->fetch() )
		{
			if( $item['homeimgthumb'] == 1 ) //image thumb
			{
				$item['imghome'] = NV_BASE_SITEURL . NV_FILES_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
			}
			elseif( $item['homeimgthumb'] == 2 ) //image file
			{
				$item['imghome'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
			}
			elseif( $item['homeimgthumb'] == 3 ) //image url
			{
				$item['imghome'] = $item['homeimgfile'];
			}
			elseif( $show_no_image ) //no image
			{
				$item['imghome'] = NV_BASE_SITEURL . 'themes/' . $global_config['site_theme'] . '/images/no_image.gif';
			}
			else
			{
				$item['imghome'] = '';
			}

			$item['link'] = $global_array_cat[$item['catid']]['link'] . '/' . $item['alias'] . '-' . $item['id'] . $global_config['rewrite_exturl'];
			$array_catpage[] = $item;
			$end_publtime = $item['publtime'];
		}
		
		$sdr->reset()
			->select('id, catid, addtime, edittime, publtime, title, alias, hitstotal')
			->from( NV_PREFIXLANG . '_' . $module_data . '_rows' );

		if( $viewcat == 'viewcat_page_new' )
		{
			$sdr->where( 'status= 1 AND inhome=1 AND publtime < ' . $end_publtime );
		}
		else
		{
			$sdr->where( 'status= 1 AND inhome=1 AND publtime > ' . $end_publtime );
		}
		$sdr->order( $order_by )->limit( $st_links );
		
		$result = $db->query( $sdr->get() );
		while( $item = $result->fetch() )
		{
			$item['link'] = $global_array_cat[$item['catid']]['link'] . '/' . $item['alias'] . '-' . $item['id'] . $global_config['rewrite_exturl'];
			$array_cat_other[] = $item;
		}

		$viewcat = 'viewcat_page_new';
		$generate_page = nv_alias_page( $page_title, $base_url, $all_page, $per_page, $page );
		$contents = call_user_func( $viewcat, $array_catpage, $array_cat_other, $generate_page );
	}
	elseif( $viewcat == 'viewcat_main_left' or $viewcat == 'viewcat_main_right' or $viewcat == 'viewcat_main_bottom' )
	{
		$array_cat = array();

		$key = 0;
		$sdr->reset()
			->select('id, listcatid, topicid, admin_id, author, sourceid, addtime, edittime, publtime, title, alias, hometext, homeimgfile, homeimgalt, homeimgthumb, allowed_rating, hitstotal, hitscm, total_rating, click_rating' )
			->where( 'status= 1 AND inhome=1' )
			->order( 'publtime DESC' );
		
		foreach( $global_array_cat as $_catid => $array_cat_i )
		{
			if( $array_cat_i['parentid'] == 0 and $array_cat_i['inhome'] == 1 )
			{
				$array_cat[$key] = $array_cat_i;

				$result = $db->query( $sdr->from( NV_PREFIXLANG . '_' . $module_data . '_' . $_catid )->limit( $array_cat_i['numlinks'] )->get() );
				while( $item = $result->fetch() )
				{
					if( $item['homeimgthumb'] == 1 )
					{
						$item['imghome'] = NV_BASE_SITEURL . NV_FILES_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
					}
					elseif( $item['homeimgthumb'] == 2 )
					{
						$item['imghome'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
					}
					elseif( $item['homeimgthumb'] == 3 )
					{
						$item['imghome'] = $item['homeimgfile'];
					}
					elseif( $show_no_image )
					{
						$item['imghome'] = NV_BASE_SITEURL . 'themes/' . $global_config['site_theme'] . '/images/no_image.gif';
					}
					else
					{
						$item['imghome'] = '';
					}


					$item['link'] = $array_cat_i['link'] . '/' . $item['alias'] . '-' . $item['id'] . $global_config['rewrite_exturl'];
					$array_cat[$key]['content'][] = $item;
				}

				++$key;
			}
		}

		$contents = viewsubcat_main( $viewcat, $array_cat );
	}
	elseif( $viewcat == 'viewcat_two_column' )
	{
		// Cac bai viet phan dau
		$array_content = $array_catpage = array();

		// cac bai viet cua cac chu de con
		$key = 0;
		
		$sdr->reset()
			->select('id, listcatid, topicid, admin_id, author, sourceid, addtime, edittime, publtime, title, alias, hometext, homeimgfile, homeimgalt, homeimgthumb, allowed_rating, hitstotal, hitscm, total_rating, click_rating')
			->where( 'status= 1 AND inhome=1' )
			->order( 'publtime DESC' );		
		foreach( $global_array_cat as $_catid => $array_cat_i )
		{
			if( $array_cat_i['parentid'] == 0 and $array_cat_i['inhome'] == 1 )
			{
				$array_catpage[$key] = $array_cat_i;
				$result = $db->query( $sdr->from( NV_PREFIXLANG . '_' . $module_data . '_' . $_catid )->limit($array_cat_i['numlinks'])->get() );

				while( $item = $result->fetch() )
				{
					if( $item['homeimgthumb'] == 1 )
					{
						$item['imghome'] = NV_BASE_SITEURL . NV_FILES_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
					}
					elseif( $item['homeimgthumb'] == 2 )
					{
						$item['imghome'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
					}
					elseif( $item['homeimgthumb'] == 3 )
					{
						$item['imghome'] = $item['homeimgfile'];
					}
					elseif( $show_no_image )
					{
						$item['imghome'] = NV_BASE_SITEURL . 'themes/' . $global_config['site_theme'] . '/images/no_image.gif';
					}
					else
					{
						$item['imghome'] = '';
					}

					$item['link'] = $array_cat_i['link'] . '/' . $item['alias'] . '-' . $item['id'] . $global_config['rewrite_exturl'];
					$array_catpage[$key]['content'][] = $item;
				}
			}

			++$key;
		}
		unset( $sql, $result );
		//Het cac bai viet cua cac chu de con
		$contents = viewcat_two_column( $array_content, $array_catpage );
	}
	elseif( $viewcat == 'viewcat_grid_new' or $viewcat == 'viewcat_grid_old' )
	{
		$order_by = ( $viewcat == 'viewcat_grid_new' ) ? ' publtime DESC' : ' publtime ASC';
		$sdr->reset()
			->select( 'COUNT(*) ')
			->from( NV_PREFIXLANG . '_' . $module_data . '_rows' )
			->where( 'status= 1 AND inhome=1' );

		$all_page = $db->query( $sdr->get() )->fetchColumn();
			
		$sdr->select( 'id, catid, topicid, admin_id, author, sourceid, addtime, edittime, publtime, title, alias, hometext, homeimgfile, homeimgalt, homeimgthumb, allowed_rating, hitstotal, hitscm, total_rating, click_rating' )
			->order( $order_by )
			->limit($per_page, ( $page - 1 ) * $per_page );
			
		$result = $db->query( $sdr->get() );
		while( $item = $result->fetch() )
		{
			if( $item['homeimgthumb'] == 1 )
			{
				$item['imghome'] = NV_BASE_SITEURL . NV_FILES_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
			}
			elseif( $item['homeimgthumb'] == 2 )
			{
				$item['imghome'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . '/' . $item['homeimgfile'];
			}
			elseif( $item['homeimgthumb'] == 3 )
			{
				$item['imghome'] = $item['homeimgfile'];
			}
			elseif( $show_no_image )
			{
				$item['imghome'] = NV_BASE_SITEURL . 'themes/' . $global_config['site_theme'] . '/images/no_image.gif';
			}
			else
			{
				$item['imghome'] = '';
			}

			$item['link'] = $global_array_cat[$item['catid']]['link'] . '/' . $item['alias'] . '-' . $item['id'] . $global_config['rewrite_exturl'];
			$array_catpage[] = $item;
		}

		$viewcat = 'viewcat_grid_new';
		$generate_page = nv_alias_page( $page_title, $base_url, $all_page, $per_page, $page );
		$contents = call_user_func( $viewcat, $array_catpage, 0, $generate_page );
	}
	elseif( $viewcat == 'viewcat_list_new' or $viewcat == 'viewcat_list_old' ) // Xem theo tieu de
	{
		$order_by = ( $viewcat == 'viewcat_list_new' ) ? 'publtime DESC' : 'publtime ASC';

		$sdr->reset()
			->select( 'COUNT(*) ')
			->from( NV_PREFIXLANG . '_' . $module_data . '_rows' )
			->where( 'status= 1 AND inhome=1' );
					
		$result_all = $db->query( 'SELECT FOUND_ROWS()' );
		list( $all_page ) = $db->sql_fetchrow( $sdr->get() );

		$sdr->select( 'id, catid, topicid, admin_id, author, sourceid, addtime, edittime, publtime, title, alias, hometext, homeimgfile, homeimgalt, homeimgthumb, allowed_rating, hitstotal, hitscm, total_rating, click_rating' )
			->order( $order_by )
			->limit($per_page, ( $page - 1 ) * $per_page );
		
		$result = $db->query( $sdr->get() );
		while( $item = $result->fetch() )
		{
			$item['imghome'] = '';

			$item['link'] = $global_array_cat[$item['catid']]['link'] . '/' . $item['alias'] . '-' . $item['id'] . $global_config['rewrite_exturl'];
			$array_catpage[] = $item;
		}

		$viewcat = 'viewcat_list_new';
		$generate_page = nv_alias_page( $page_title, $base_url, $all_page, $per_page, $page );
		$contents = call_user_func( $viewcat, $array_catpage, 0, ( $page - 1 ) * $per_page, $generate_page );
	}

	if( ! defined( 'NV_IS_MODADMIN' ) and $contents != '' and $cache_file != '' )
	{
		nv_set_cache( $cache_file, $contents );
	}
}

if( $page > 1 )
{
	$page_title .= ' ' . NV_TITLEBAR_DEFIS . ' ' . $lang_global['page'] . ' ' . $page;
}

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme( $contents );
include NV_ROOTDIR . '/includes/footer.php';

?>