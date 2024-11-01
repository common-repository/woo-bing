<?php 
class WooBing_Admin_Feed {
	
	function __construct() {
		add_action( 'admin_init', array( $this, 'register_post_type' ) );
		add_action( 'admin_init', array( $this, 'check_categori_fetch' ) );
		add_action( 'admin_init', array( $this, 'new_feed' ) );
        add_action( 'template_redirect', array( $this, 'txt_download' ) );
        add_action( 'admin_init', array( $this, 'feed_delete' ) );
	}

    function feed_delete() {
        if( ! isset( $_GET['page'] ) || ! isset( $_GET['woobing_tab'] ) || ! isset( $_GET['action'] ) ) {
            return;
        }

        if ( $_GET['page'] != 'product_woobing' || $_GET['woobing_tab'] != 'woobing_multiple' || $_GET['action'] != 'delete' ) {
            return;
        }

        $feed_id = isset( $_GET['feed_id'] ) ? intval( $_GET['feed_id'] ) : 0;

        if ( ! $feed_id ) {
            return;
        }

        wp_delete_post( $feed_id, true );

        $url_feed_list   = admin_url( 'edit.php?post_type=product&page=product_woobing&woobing_tab=woobing_feed_lists' );
        wp_redirect( $url_feed_list );
        exit();
    }

    function txt_download() {
        if ( ! isset( $_GET['woobing_feed_download'] ) || ! isset( $_GET['nonce'] ) ) {
            return;
        }

        $feed_id = isset( $_GET['feed_id'] ) ? intval( $_GET['feed_id'] ) : 0;

        if ( ! $feed_id ) {
            return;
        }

        $post_feed = get_post( $feed_id );

        global $wpdb, $wp_query, $post;

        // Don't cache feed under WP Super-Cache
        define( 'DONOTCACHEPAGE', TRUE );

        // Cater for large stores
        $wpdb->hide_errors();
        @set_time_limit( 0 );
        while ( ob_get_level() ) {
            @ob_end_clean();
        }

        if ( $_GET['woobing_feed_download'] ) {
            header( 'Content-Disposition: attachment; filename="WooBing_Product_List.txt"' );
        } else {
            header( 'Content-Disposition: inline; filename="WooBing_Product_List.txt"' );
        }

        echo $post_feed->post_content;
        exit();
    }

	function new_feed() {
        if( ! isset( $_POST['woobing_submit_feed'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['feed_nonce'], 'woobing_feed_nonce' ) ) {
            return;
        }

        $post   = $_POST;
        $feed_id = isset( $post['id'] ) ? intval( $post['id'] ) : false;

        $arg = array(
            'post_type'    => 'woobing_feed',
            'post_title'   => $post['post_title'],
            'post_content' => $this->get_txt( $post ),
            'post_status'  => 'publish'
        );

        if ( $feed_id ) {
            $arg['ID'] = $feed_id;
            $post_id = wp_update_post( $arg );
        } else {
            $post_id = wp_insert_post( $arg );
        }

        if ( $post_id ) {
            $this->update_feed_meta( $post_id, $post );
        }

        $url_feed_list   = admin_url( 'edit.php?post_type=product&page=product_woobing&woobing_tab=woobing_feed_lists' );
        wp_redirect( $url_feed_list );
        exit();
    }

    function get_txt( $post ) {
    	$header_col = array();
    	//header( 'Content-Disposition: attachment; filename="E-Commerce_Product_List.txt"' );
    	$header  = $this->get_header( $post, $header_col );
   
        $txt_content = $this->content( $post, $header_col );
        return $header . $txt_content;
    }

    function get_header( $post, &$header_col ) {

    	// Bing doesn't like foreign chars
		setlocale(LC_CTYPE, 'en_US.UTF-8');

		$header = "id\ttitle\tlink\tprice\tdescription\timage_link\tseller_name";

		$header_col = array( 'id', 'title', 'link', 'price', 'description', 'image_link', 'seller_name' );

		// Optional fields
		if ( isset ( $post['mpn'] ) && $post['mpn'] == 'on' ) {
			$header .= "\tmpn";
			array_push( $header_col, 'mpn' );
		}
		if ( isset ( $post['brand'] ) && ! empty( $post['mpn'] ) ) {
			$header .= "\tbrand";
			array_push( $header_col, 'brand' );
		}
		if ( isset ( $post['gender'] ) && $post['gender'] != '-1' ) {
			$header .= "\tgender";
			array_push( $header_col, 'gender' );
		}
		if ( isset ( $post['age_group'] ) && $post['age_group'] != '-1' ) {
			$header .= "\tage_group";
			array_push( $header_col, 'age_group' );
		}
		if ( isset ( $post['color'] ) && $post['color'] != '-1' ) {
			$header .= "\tcolor";
			array_push( $header_col, 'color' );
		}
		if ( isset ( $post['size'] ) && $post['size'] != '-1' ) {
			$header .= "\tsize";
			array_push( $header_col, 'size' );
		}
		if ( isset ( $post['variable_products'] ) && $post['variable_products'] == 'yes' ) {
			$header .= "\titem_group_id";
			array_push( $header_col, 'variable_products' );
		}
		if ( isset ( $post['availability'] ) && $post['availability'] != '-1' ) {
			$header .= "\tavailability";
			array_push( $header_col, 'availability' );
		}
		if ( isset ( $post['google_product_category'] ) && $post['google_product_category'] != '0' ) {
			$header .= "\tproduct_category";
			array_push( $header_col, 'google_product_category' );
		}
		if ( isset ( $post['condition'] ) && $post['condition'] != '-1' ) {
			$header .= "\tcondition";
			array_push( $header_col, 'condition' );
		}
		if ( isset ( $post['expiration_date'] ) && ! empty( $post['expiration_date'] ) ) {
			$header .= "\texpiration_date";
			array_push( $header_col, 'expiration_date' );
		}
		if ( isset ( $post['product_type'] ) && ! empty( $post['product_type'] ) ) {
			$header .= "\tproduct_type";
			array_push( $header_col, 'product_type' );
		}
		if ( isset ( $post['sale_price'] ) && $post['sale_price'] == 'yes' ) {
			$header .= "\tsale_price";
			array_push( $header_col, 'sale_price' );
		}
		if ( isset ( $post['sale_price_effective_date'] ) && $post['sale_price_effective_date'] == 'yes' ) {
			$header .= "\tsale_price_effective_date";
			array_push( $header_col, 'sale_price_effective_date' );
		}
		if ( isset ( $post['custom_label_0'] ) && ! empty( $post['custom_label_0'] ) ) {
			$header .= "\tcustom_label_0";
			array_push( $header_col, 'custom_label_0' );
		}
		if ( isset ( $post['custom_label_1'] ) && ! empty( $post['custom_label_1'] ) ) {
			$header .= "\tcustom_label_1";
			array_push( $header_col, 'custom_label_1' );
		}

		if ( isset ( $post['custom_label_2'] ) && ! empty( $post['custom_label_2'] ) ) {
			$header .= "\tcustom_label_2";
			array_push( $header_col, 'custom_label_2' );
		}
		if ( isset ( $post['custom_label_3'] ) && ! empty( $post['custom_label_3'] ) ) {
			$header .= "\tcustom_label_3";
			array_push( $header_col, 'custom_label_3' );
		}
		if ( isset ( $post['custom_label_4'] ) && ! empty( $post['custom_label_4'] ) ) {
			$header .= "\tcustom_label_4";
			array_push( $header_col, 'custom_label_4' );
		}
		
		$header .= "\r\n";

		return $header;
    }

    function content( $post, $header_col ) {
    	
    	if ( isset( $post['all_products'] ) ) {
            $offset = 20;
            $products = woobing_get_products( 20 );

            while( count( $products ) ) {
                $new_some_products = woobing_get_products( 20, $offset );
                $products          = array_merge( $products, $new_some_products );
                if ( ! count( $new_some_products ) ) {
                    break;
                }
                $offset            = $offset + 20;
            }
            $products = wp_list_pluck( $products, 'ID' );
        } else {
            $products = $post['products'];
        }
        
        if ( ! count( $products ) ) {
            return false;
        }

        $product_cat = get_option( 'woobing_google_product_type' );
        $content = '';
        foreach ( $products as $key => $product_id ) {
   
            $wc_product  = wc_get_product( $product_id );
            $variation = false;

            if ( $wc_product->post->post_status != 'publish' ) {
                continue;
            }

            $enable_variable_product = ( isset( $post['variable_products'] ) && $post['variable_products'] == 'yes' ) ? true : false;
            
            if ( $wc_product->product_type == 'variable' && $enable_variable_product ) {
                $variable       = new WC_Product_Variable( $wc_product );
                
                $get_variations = $variable->get_available_variations();
                $get_attrs      = $variable->get_variation_attributes();
                
                if ( $get_variations ) {
                    $variation = true;
                    foreach ( $get_variations as $variable_name => $attr ) {
                        if ( ! $attr['variation_is_active'] || ! $attr['variation_is_visible'] ) {
                            continue;
                        }
                        $variation_content = $this->text_for_product_variation( $post, $wc_product,  $attr, $get_attrs, $product_cat, $header_col );
                        $content .= $variation_content;
                    }
                }
            }

            if ( $variation ) {
                continue;
            }

            $seller_name        = $this->bing_escap( $post['seller_name'] );
            $size_attr          = $this->get_size_attr( $post, $product_id, $wc_product );
            $color_attr         = $this->get_color_attr( $post, $product_id, $wc_product );
            $sale_price         = $wc_product->get_sale_price();
            $post_title         = $this->bing_escap( $wc_product->post->post_title );
            $description        = $this->bing_escap( $wc_product->post->post_content );
            $link               = $wc_product->get_permalink();
            $feed_image_url     = wp_get_attachment_url( $wc_product->get_image_id() );
            $condition          = $this->get_condition( $post, $product_id );
            $availability       = $this->get_availability( $post, $product_id );
            $category           = $this->bing_escap( $this->get_category( $post, $product_id, $product_cat ) );
            $type               = $this->bing_escap( $post['product_type'] ); //$this->get_type( $post, $product_id,  $product_cat );
            $availability_date  = $this->get_availability_date( $post, $product_id );
            $availability_value = $this->get_availability_value( $availability_date );
            $sku_as_mpn         = $this->get_sku_as_mpn( $post, $product_id, $wc_product );
            $gender             = $this->get_gender( $post, $product_id );
            $age_group          = $this->get_age_group( $post, $product_id );
            $custom_label_0     = $this->bing_escap( $this->get_custom_label_0( $post, $product_id ) );
            $custom_label_1     = $this->bing_escap( $this->get_custom_label_1( $post, $product_id ) );
            $custom_label_2     = $this->bing_escap( $this->get_custom_label_2( $post, $product_id ) );
            $custom_label_3     = $this->bing_escap( $this->get_custom_label_3( $post, $product_id ) );
            $custom_label_4     = $this->bing_escap( $this->get_custom_label_4( $post, $product_id ) );
            $brand              = $this->bing_escap( $this->get_brand( $post, $product_id ) );
            $expiration_date    = $this->get_expiration_date( $post, $product_id );
            $price       = $wc_product->get_price();
            $salse_price = $wc_product->get_sale_price();
            $sale_price_effective_date = $this->get_sale_price_effective_date( $product_id );
            $description = strip_shortcodes( $this->strip_line_break( $description ) );
            
            $content .= "$wc_product->id";
            $content .= "\t$post_title";
            $content .= "\t$link";
            $content .= "\t$price";
            $content .= "\t$description";
            $content .= "\t$feed_image_url";
            $content .= "\t$seller_name";
            $content .= in_array( 'mpn', $header_col ) ? "\t$sku_as_mpn" : "";
            $content .= in_array( 'brand', $header_col ) ? "\t$brand" : "";
            $content .= in_array( 'gender', $header_col ) ? "\t$gender" : "";
            $content .= in_array( 'age_group', $header_col ) ? "\t$age_group" : "";
            $content .= in_array( 'color', $header_col ) ? "\t$color_attr" : "";
            $content .= in_array( 'size', $header_col ) ? "\t$size_attr" : "";
            $content .= in_array( 'variable_products', $header_col ) ? "\t" : "";
            $content .= in_array( 'availability', $header_col ) ? "\t$availability" : "";
            $content .= in_array( 'google_product_category', $header_col ) ? "\t$category" : "";
            $content .= in_array( 'condition', $header_col ) ? "\t$condition" : "";
            $content .= in_array( 'expiration_date', $header_col ) ? "\t$expiration_date" : "";
            $content .= in_array( 'product_type', $header_col ) ? "\t$type" : "";
            $content .= in_array( 'sale_price', $header_col ) ? "\t$price" : "";
            $content .= in_array( 'sale_price_effective_date', $header_col ) ? "\t$sale_price_effective_date" : "";
            $content .= in_array( 'custom_label_0', $header_col ) ? "\t$custom_label_0" : "";
            $content .= in_array( 'custom_label_1', $header_col ) ? "\t$custom_label_1" : "";
            $content .= in_array( 'custom_label_2', $header_col ) ? "\t$custom_label_2" : "";
            $content .= in_array( 'custom_label_3', $header_col ) ? "\t$custom_label_3" : "";
            $content .= in_array( 'custom_label_4', $header_col ) ? "\t$custom_label_4\t\r\n" : "\t\r\n";
        }

        return $content;
    }

    function text_for_product_variation( $post, $wc_product,  $attr, $get_attrs, $product_cat, $header_col ) {

        $custom_attrs       = array();
        $product_id         = $wc_product->id; 
        $variation_id       = $attr['variation_id'];
        $brand              = $this->bing_escap( $this->get_brand( $post, $product_id ) );
        
        $size_attr          = isset( $attr['attributes']['attribute_size'] ) && ! empty( $attr['attributes']['attribute_size'] ) ? $attr['attributes']['attribute_size'] : false;
        if ( ! $size_attr ) {
            $size_attr      = isset( $attr['attributes']['attribute_pa_size'] ) && ! empty( $attr['attributes']['attribute_pa_size'] ) ? $attr['attributes']['attribute_pa_size'] : false;  
        }
        
        $color_attr         = isset( $attr['attributes']['attribute_color'] ) && ! empty( $attr['attributes']['attribute_color'] ) ? $attr['attributes']['attribute_color'] : false;
        if ( ! $color_attr ) {
            $color_attr      = isset( $attr['attributes']['attribute_pa_color'] ) && ! empty( $attr['attributes']['attribute_pa_color'] ) ? $attr['attributes']['attribute_pa_color'] : false;  
        }
        
        $currency           = get_woocommerce_currency();
        $sale_price         = ! empty( $attr['display_price'] ) ? $attr['display_price'] . ' ' . $currency : false;
        $post_title         = $wc_product->post->post_title;
        $description        = strip_tags( html_entity_decode( stripslashes( nl2br( $attr['variation_description'] ) ) ) );
        $link               = $wc_product->get_permalink();
        $feed_image_url     = ! empty( $attr['image_src'] ) ? $this->bing_escap( $attr['image_src'] ) : false;
        $condition          = $this->get_condition( $post, $product_id );
        $availability       = $this->get_availability( $post, $product_id );
        $category           = $this->get_category( $post, $product_id, $product_cat );
        $type               = $this->bing_escap( $post['product_type'] );
        $availability_date  = $this->get_availability_date( $post, $product_id );
        $availability_value = $this->get_availability_value( $availability_date );
        $sku_as_mpn         = $attr['sku'] ? $attr['sku'] : false;
        $gender             = $this->get_gender( $post, $product_id );
        $age_group          = $this->get_age_group( $post, $product_id );
        $custom_label_0     = $this->get_custom_label_0( $post, $product_id );
        $custom_label_1     = $this->get_custom_label_1( $post, $product_id );
        $custom_label_2     = $this->get_custom_label_2( $post, $product_id );
        $custom_label_3     = $this->get_custom_label_3( $post, $product_id );
        $custom_label_4     = $this->get_custom_label_4( $post, $product_id );
        $expiration_date    = $this->get_expiration_date( $post, $product_id );
        
        $price       = ! empty( $attr['display_regular_price'] ) ? $attr['display_regular_price'] : false;
        $seller_name = $this->bing_escap( $post['seller_name'] );
        $sale_price_effective_date = $this->get_sale_price_effective_date( $product_id );
        $description = $this->strip_line_break( $description );
        $category = $this->strip_line_break( $category );
        
        $content  = "$variation_id";
        $content .= "\t$post_title";
        $content .= "\t$link";
        $content .= "\t$price";
        $content .= "\t$description";
        $content .= "\t$feed_image_url";
        $content .= "\t$seller_name";
        $content .= in_array( 'mpn', $header_col ) ? "\t$sku_as_mpn" : "";
        $content .= in_array( 'brand', $header_col ) ? "\t$brand" : "";
        $content .= in_array( 'gender', $header_col ) ? "\t$gender" : "";
        $content .= in_array( 'age_group', $header_col ) ? "\t$age_group" : "";
        $content .= in_array( 'color', $header_col ) ? "\t$color_attr" : "";
        $content .= in_array( 'size', $header_col ) ? "\t$size_attr" : "";
        $content .= in_array( 'variable_products', $header_col ) ? "\t$product_id" : "";
        $content .= in_array( 'availability', $header_col ) ? "\t$availability" : "";
        $content .= in_array( 'google_product_category', $header_col ) ? "\t$category" : "";
        $content .= in_array( 'condition', $header_col ) ? "\t$condition" : "";
        $content .= in_array( 'expiration_date', $header_col ) ? "\t$expiration_date" : "";
        $content .= in_array( 'product_type', $header_col ) ? "\t$type" : "";
        $content .= in_array( 'sale_price', $header_col ) ? "\t$price" : "";
        $content .= in_array( 'sale_price_effective_date', $header_col ) ? "\t$sale_price_effective_date" : "";
        $content .= in_array( 'custom_label_0', $header_col ) ? "\t$custom_label_0" : "";
        $content .= in_array( 'custom_label_1', $header_col ) ? "\t$custom_label_1" : "";
        $content .= in_array( 'custom_label_2', $header_col ) ? "\t$custom_label_2" : "";
        $content .= in_array( 'custom_label_3', $header_col ) ? "\t$custom_label_3" : "";
        $content .= in_array( 'custom_label_4', $header_col ) ? "\t$custom_label_4\t\r\n" : "\t\r\n";

        return $content; 
    }

    function strip_line_break( $text ) {
        $text = strip_tags( $text );
        $text = str_replace("\n", "", $text );
        return str_replace("\r", "", $text );
    }


    function bing_escap( $string ) {

        $string = html_entity_decode( $string, ENT_HTML401 | ENT_QUOTES ); // Convert any HTML entities
        $string = iconv(
            'UTF-8',
            'ASCII//TRANSLIT',
            $string
        );
        $doneescape = false;
        if ( stristr( $string, '"' ) ) {
            $string = str_replace( '"', '""', $string );
            $string = "\"$string\"";
            $doneescape = true;
        }
        $string = str_replace( "\n", ' ', $string );
        $string = str_replace( "\r", ' ', $string );
        if ( stristr( $string,  "\t"  ) && ! $doneescape ) {
            $string = "\"$string\"";
        }
        return $string;
    }


    function get_sale_price_effective_date( $product_id ) {
    	$sale_price_dates_from 	= ( $date = get_post_meta( $product_id, '_sale_price_dates_from', true ) ) ? $this->get_availability_value( date_i18n( 'Y-m-d H:i:s', $date ) ) : false;
    	
    	if ( ! $sale_price_dates_from ) {
    		return false;
    	}
		$sale_price_dates_to 	= ( $date = get_post_meta( $product_id, '_sale_price_dates_to', true ) ) ? $this->get_availability_value( date_i18n( 'Y-m-d H:i:s', $date ) ) : false;

		if ( ! $sale_price_dates_to ) {
    		return false;
    	}

    	return $sale_price_dates_from .'/'. $sale_price_dates_to;
    }

    /**
	 * Helper function for displaying excerpts
	 *
	 * @since 0.1
	 * @param string $text
	 * @param int $length
	 * @param string $append
	 * @return string
	 */
	function str_excerpt( $text, $length, $append = '' ) {
	    $text  = wp_strip_all_tags( $text, true );

	    if ( function_exists( 'mb_strlen' ) ) {
	        $count = mb_strlen( $text );
	        $text  = mb_substr( $text, 0, $length );
	    } else {
	        $count = strlen( $text );
	        $text  = substr( $text, 0, $length );
	    }

	    if ( $count > $length ) {
	        $text = $text . $append;
	    }

	    return $text;
	}

    function get_condition( $post, $product_id ) {
    	//required attribute
        $condition_ind = get_post_meta( $product_id, '_condition', true );
        if ( $condition_ind == '-1' ) {
            $condition      = $post['condition'];
        } else {
            $condition  = empty( $condition_ind ) ? $post['condition'] : $condition_ind;
        }

        return $condition;
    }

    function get_availability( $post, $product_id ) {
    	//required attribute
        $avaibility_ind = get_post_meta( $product_id, '_availability', true );
        if ( $avaibility_ind == 'default' ) {
            $availability   = $post['availability'];
        } else {
            $availability   = $avaibility_ind;
        }

        return $availability;
    }

    function get_category( $post, $product_id, $product_cat ) {
        $pro_cat_ind = get_post_meta( $product_id, '_google_product_category', true );
        if ( $pro_cat_ind == 'default' ) {
            $category = $post['google_product_category'] ? $product_cat[$post['google_product_category']] : false;
        } else {
            if ( empty( $pro_cat_ind ) ) {
                $category = false;
            } else {
                $category = $product_cat[$pro_cat_ind];
            }
        }

        return $category;
    }

    function get_type( $post, $product_id, $product_cat ) {
    	
    	$pro_typ_ind = get_post_meta( $product_id, '_product_type', true );
        
        if ( $pro_typ_ind == 'default' ) {
            $type = $post['product_type'] ? $product_cat[$post['product_type']] : false;
            $type = $type ? str_replace( "&", "&amp;", $type ) : false;
            $type = $type ? str_replace( ">", "&gt;", $type ) : false;
        } else {
            if ( empty( $pro_typ_ind ) ) {
                $type = false;
            } else {
                $type = $product_cat[$pro_typ_ind];
                $type = $type ? str_replace( "&", "&amp;", $type ) : false;
                $type = $type ? str_replace( ">", "&gt;", $type ) : false;
            }
        }

        return $type;
    }

    function get_availability_date( $post, $product_id ) {
    	$availability_date_ind = get_post_meta( $product_id, '_availability_date_default', true );
        if ( $availability_date_ind == 'default' ) {
            $availability_date = !empty( $post['availability_date'] ) ? $post['availability_date'] : false;
        } else {
            $availability_date = get_post_meta( $product_id, '_availability_date', true );
            $availability_date = empty( $availability_date ) ? false : $availability_date;
        }

        return $availability_date;
    }

    function get_expiration_date( $post, $product_id ) {
        $expiration_date_ind = get_post_meta( $product_id, '_expiration_date_default', true );
        if ( $expiration_date_ind == 'default' ) {
            $expiration_date = !empty( $post['expiration_date'] ) ? $post['expiration_date'] : false;
        } else {
            $expiration_date = get_post_meta( $product_id, '_expiration_date', true );
            $expiration_date = empty( $expiration_date ) ? false : $expiration_date;
        }

        return $expiration_date;
    }

    function get_availability_value( $availability_date ) {
    	$availability_value = '';

    	if ( $availability_date ) {
            $tz_offset = get_option( 'gmt_offset' );
            $availability_value = $availability_date.'T00:00:00' . sprintf( '%+03d', $tz_offset ) . '00';
        }

        return $availability_value;
    }

    function get_sku_as_mpn( $post, $product_id, $wc_product ) {
    	$sku_ind = get_post_meta( $product_id, '_mpn_default', true );

        $sku = $wc_product->get_sku();
        $sku = ! empty( $sku ) ? $sku : false;

        if ( $sku_ind == 'default' ) {
            $mpn = isset( $post['mpn'] ) ? true : false;
            $sku_as_mpn    = $mpn ? $sku : false;
        } else {
            $sku_as_mpn = get_post_meta( $product_id, '_mpn', true );
            
            if ( empty( $sku_as_mpn ) ) {
                $sku_as_mpn = $sku ? $sku : false;
            } 
        }

        return $sku_as_mpn;
    }

    function get_gender( $post, $product_id ) {
    	$gender_ind = get_post_meta( $product_id, '_gender', true );
        if ( $gender_ind == 'default' ) {
            $gender = $post['gender'] == '-1' ? false : $post['gender'];
        } else {
            $gender = ( $gender_ind == '-1' ) ? false : $gender_ind;
        }

        return $gender;
    }

    function get_age_group( $post, $product_id ) {
    	$age_group_ind = get_post_meta( $product_id, '_age_group', true );
        if ( $age_group_ind == 'default' ) {
            $age_group = $post['age_group'] == '-1' ? false : $post['age_group'];
        } else {
            $age_group = ( $age_group_ind == '-1' ) ? false : $age_group_ind;
        }

        return $age_group;
    }

    function get_size_type( $post, $product_id ) {
    	$size_type_ind = get_post_meta( $product_id, '_size_type', true );
        if ( $size_type_ind == 'default' ) {
            $size_type = $post['size_type'] == '-1' ? false : $post['size_type'];
        } else {
            $size_type = ( $size_type_ind == '-1' ) ? false : $size_type_ind;
        }

        return $size_type;
    }

    function get_custom_label_0( $post, $product_id ) {
    	$custom_label_0_ind = get_post_meta( $product_id, '_custom_label_0_default', true );
        if ( $custom_label_0_ind == 'default' ) {
            $custom_label_0 = ! empty( $post['custom_label_0'] ) ? $post['custom_label_0'] : false;
        } else {
            $custom_label_0 = get_post_meta( $product_id, '_custom_label_0', true );
            $custom_label_0 = empty( $custom_label_0 ) ? false : $custom_label_0;
        }

        return $custom_label_0;
    }

    function get_custom_label_1( $post, $product_id ) {
    	$custom_label_1_ind = get_post_meta( $product_id, '_custom_label_1_default', true );
        if ( $custom_label_1_ind == 'default' ) {
            $custom_label_1 = ! empty( $post['custom_label_1'] ) ? $post['custom_label_1'] : false;
        } else {
            $custom_label_1 = get_post_meta( $product_id, '_custom_label_1', true );
            $custom_label_1 = empty( $custom_label_1 ) ? false : $custom_label_1;
        }

        return $custom_label_1;
    }

    function get_custom_label_2( $post, $product_id ) {
    	$custom_label_2_ind = get_post_meta( $product_id, '_custom_label_2_default', true );
        if ( $custom_label_2_ind == 'default' ) {
            $custom_label_2 = ! empty( $post['custom_label_2'] ) ? $post['custom_label_2'] : false;
        } else {
            $custom_label_2 = get_post_meta( $product_id, '_custom_label_2', true );
            $custom_label_2 = empty( $custom_label_2 ) ? false : $custom_label_2;
        }

        return $custom_label_2;
    }

    function get_custom_label_3( $post, $product_id ) {
    	$custom_label_3_ind = get_post_meta( $product_id, '_custom_label_3_default', true );
        if ( $custom_label_3_ind == 'default' ) {
            $custom_label_3 = ! empty( $post['custom_label_3'] ) ? $post['custom_label_3'] : false;
        } else {
            $custom_label_3 = get_post_meta( $product_id, '_custom_label_3', true );
            $custom_label_3 = empty( $custom_label_3 ) ? false : $custom_label_3;
        }

        return $custom_label_3;
    }

    function get_custom_label_4( $post, $product_id ) {
    	$custom_label_4_ind = get_post_meta( $product_id, '_custom_label_4_default', true );
        if ( $custom_label_4_ind == 'default' ) {
            $custom_label_4 = ! empty( $post['custom_label_4'] ) ? $post['custom_label_4'] : false;
        } else {
            $custom_label_4 = get_post_meta( $product_id, '_custom_label_4', true );
            $custom_label_4 = empty( $custom_label_4 ) ? false : $custom_label_4;
        }

        return $custom_label_4;
    }

    function get_brand( $post, $product_id ) {
	    $brand_ind = get_post_meta( $product_id, '_brand_default', true );
        if ( $brand_ind == 'default' ) {
            $brand = ! empty( $post['brand'] ) ? $post['brand'] : false;
        } else {
            $brand = get_post_meta( $product_id, '_brand', true );
            $brand = empty( $brand ) ? false : $brand;
        }

        return $brand;
    }

    function get_additional_images( $wc_product ) {
        $additional_images = array();
        foreach ( $wc_product->get_gallery_attachment_ids() as $key => $link_id ) {
            $additional_images[] =  wp_get_attachment_url( $link_id ); 
            if ( count( $additional_images ) > 9 ) {
                break;
            }
        }
/*	    $main_thumbnail = get_post_meta( $product_id, '_thumbnail_id', true );
        $images = get_children(
            array(
                'post_parent'    => $product_id,
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'exclude'        => isset($main_thumbnail) ? $main_thumbnail : '',
                'order'          => 'ASC',
                'orderby'        => 'menu_order',
            )
        );

        $additional_images = array();
        if ( is_array( $images ) && count( $images ) ) {
            foreach ( $images as $image ) {
                $full_image_src      = wp_get_attachment_image_src( $image->ID, 'original' );
                $additional_images[] = $full_image_src[0];
            }
        }*/

        return $additional_images;
    }

    function woobing_is_product_attribute_taxonomy( $attr, $porduct_obj ) {

	    $attributes = $porduct_obj->get_attributes();

	    $attr = sanitize_title( $attr );

	    if ( isset( $attributes[ $attr ] ) || isset( $attributes[ 'pa_' . $attr ] ) ) {

	        $attribute = isset( $attributes[ $attr ] ) ? $attributes[ $attr ] : $attributes[ 'pa_' . $attr ];
	        if ( $attribute['is_taxonomy'] ) {
	            return true;
	        } else {
	         return false;
	        }
	    }
	    return false;
	}

    function get_size_attr( $post, $product_id, $wc_product ) {
    	$size = $wc_product->get_attribute('size');

    	if ( isset( $post['size'] ) && ! empty( $size ) ) {
            $size = str_replace(' ', '', $size );
            $size_attr = $this->woobing_is_product_attribute_taxonomy( 'size', $wc_product ) ? str_replace( ',', ', ', $size ) : str_replace( '|', ', ', $size );
        } else {
            $size_attr = false;
        }

    	$size_ind = get_post_meta( $product_id, '_size_default', true );
        if ( $size_ind != 'default' ) {
            $size = get_post_meta( $product_id, '_size', true );
            if ( empty( $size ) ) {
                $size_attr = false;
            } else {
                $size = str_replace(' ', '', $size );
                $size_attr = str_replace( ',', ', ', $this->str_excerpt( $size, 100 ) );
            }
        }

        return $size_attr;
    }

    function get_color_attr( $post, $product_id, $wc_product ) {

    	$color = $wc_product->get_attribute('color');
            
        if ( isset( $post['color'] ) && ! empty( $color ) ) {
            $color = str_replace(' ', '', $color );
            $color_attr = $this->woobing_is_product_attribute_taxonomy( 'color', $wc_product ) ? str_replace( ',', '/', $color ) : str_replace( '|', '/', $color );
        } else {
            $color_attr = false;
        }

    	$color_ind = get_post_meta( $product_id, '_color_default', true );
        if ( $color_ind != 'default' ) {
            $color = get_post_meta( $product_id, '_color', true );
            if ( empty( $color ) ) {
                $color_attr = false;
            } else {
                $color = str_replace(' ', '', $color );
                $color_attr = str_replace( ',', '/', $this->str_excerpt( $color, 100 ) );
            }
        }

        return $color_attr;
    }

    function update_feed_meta( $post_id, $post ) {

        $all_products = isset( $post['all_products'] ) ? $post['all_products'] : 0;
        update_post_meta( $post_id, '_all_products', $all_products );

        $seller_name = isset( $post['seller_name'] ) ? $post['seller_name'] : '';
        update_post_meta( $post_id, '_seller_name', $seller_name );

        $products = isset( $post['products'] ) ? $post['products'] : array();
        update_post_meta( $post_id, '_products', $products );

        $var_products = isset( $post['variable_products'] ) ? $post['variable_products'] : 'no';
        update_post_meta( $post_id, '_woobing_include_variable_products', $var_products );

        $google_product_category = isset( $post['google_product_category'] ) ? $post['google_product_category'] : '';
        update_post_meta( $post_id, '_google_product_category', $google_product_category );

        $product_type = isset( $post['product_type'] ) ? $post['product_type'] : '';
        update_post_meta( $post_id, '_product_type', $product_type );

        $availability = isset( $post['availability'] ) ? $post['availability'] : '';
        update_post_meta( $post_id, '_availability', $availability );

        $condition = isset( $post['condition'] ) ? $post['condition'] : '';
        update_post_meta( $post_id, '_condition', $condition );

        $brand = isset( $post['brand'] ) ? $post['brand'] : '';
        update_post_meta( $post_id, '_brand', $brand );

        $mpn = isset( $post['mpn'] ) ? $post['mpn'] : '';
        update_post_meta( $post_id, '_mpn', $mpn );

        $gender = isset( $post['gender'] ) ? $post['gender'] : '';
        update_post_meta( $post_id, '_gender', $gender );

        $age_group = isset( $post['age_group'] ) ? $post['age_group'] : '';
        update_post_meta( $post_id, '_age_group', $age_group );

        $color = isset( $post['color'] ) ? $post['color'] : '';
        update_post_meta( $post_id, '_color', $color );

        $size = isset( $post['size'] ) ? $post['size'] : '';
        update_post_meta( $post_id, '_size', $size );

        $expiration_date = isset( $post['expiration_date'] ) ? $post['expiration_date'] : '';
        update_post_meta( $post_id, '_expiration_date', $expiration_date );

        $sale_price = isset( $post['sale_price'] ) ? $post['sale_price'] : 'no';
        update_post_meta( $post_id, '_sale_price', $sale_price );

        $sale_price_effective_date = isset( $post['sale_price_effective_date'] ) ? $post['sale_price_effective_date'] : 'no';
        update_post_meta( $post_id, '_sale_price_effective_date', $sale_price_effective_date );

        $custom_label_0 = isset( $post['custom_label_0'] ) ? $post['custom_label_0'] : '';
        update_post_meta( $post_id, '_custom_label_0', $custom_label_0 );

        $custom_label_1 = isset( $post['custom_label_1'] ) ? $post['custom_label_1'] : '';
        update_post_meta( $post_id, '_custom_label_1', $custom_label_1 );

        $custom_label_2 = isset( $post['custom_label_2'] ) ? $post['custom_label_2'] : '';
        update_post_meta( $post_id, '_custom_label_2', $custom_label_2 );

        $custom_label_3 = isset( $post['custom_label_3'] ) ? $post['custom_label_3'] : '';
        update_post_meta( $post_id, '_custom_label_3', $custom_label_3 );

        $custom_label_4 = isset( $post['custom_label_4'] ) ? $post['custom_label_4'] : '';
        update_post_meta( $post_id, '_custom_label_4', $custom_label_4 );
    }

    function register_post_type() {
        register_post_type( 'woobing_feed', array(
            'label'               => __( 'Bing Feed', 'woobing' ),
            'public'              => false,
            'show_in_admin_bar'   => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_in_admin_bar'   => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'rewrite'             => array('slug' => ''),
            'query_var'           => true,
            'supports'            => array('title', 'editor'),
        ));
    }

	function check_categori_fetch() {

        $feed_cat_fetch_time = get_option( 'woogbing_google_product_type_fetch_time', false );
        if ( ! $feed_cat_fetch_time ) {
            $this->store_google_product_type();
            return;
        }

        $cat = get_option( 'woobing_google_product_type' );
        if ( ! $cat || ! count( $cat ) || empty( $cat ) ) {
            $this->store_google_product_type();
            return;
        }
        $minute_diff = $this->get_minute_diff( current_time( 'mysql' ), $feed_cat_fetch_time );

        if ( $minute_diff > 600 ) {
            $this->store_google_product_type();
        }
   
	}

	function store_google_product_type() {
        $cat = $this->get_bing_product_type();
        $cat = $cat ? $cat : array();
        update_option( 'woobing_google_product_type', $cat );
        update_option( 'woobing_google_product_type_fetch_time', current_time( 'mysql' ) );
    }

    function get_minute_diff( $current_time, $request_time ) {
	    $current_time = new DateTime( $current_time );
	    $request_time = new DateTime( $request_time );
	    $interval     = $request_time->diff( $current_time );
	    $day          = $interval->d ? $interval->d * 24 * 60 : 0;
	    $hour         = $interval->h ? $interval->h * 60 : 0;
	    $minute       = $interval->i ? $interval->i : 0;
	    $total_minute = $day + $hour + $minute;

	    return $total_minute;
	}

	function get_bing_product_type() {
	    $request = wp_remote_get( 'http://fp.advertising.microsoft.com/wwdocs/user/search/hosted/en-us/Bing-Category-Taxonomy-US.txt' );
	    
	    if ( is_wp_error( $request ) || ! isset( $request['response']['code'] ) || '200' != $request['response']['code'] ) {
	        return array();
	    }
	    $taxonomies = explode( "\n", $request['body'] );
	    // Strip the comment at the top
	    array_shift( $taxonomies );
	    // Strip the extra newline at the end
	    array_pop( $taxonomies );
	    $taxonomies = array_merge( array( __( '-Select-', 'woobing' ) ), $taxonomies );
	    return $taxonomies;
	}

}

new WooBing_Admin_Feed();