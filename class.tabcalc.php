<?php

  class zTabcalc {

	public function doHTML($par=array(),$var=array(),$mf=array(),$re=false) {
	global $wpdb;
		$html = '';

		// draw form
		$html.= '<form action="" method="post" class="mf" >';

			// sizes
			$html.= '
				<div class="mf__field mf__field--dbl" ><h4>Berechnungstool.</h4>Benötigte Menge nach Abdeckfläche</div>
				<div class="mf__field mf__field--dbl" ><b>Angaben zu der Fläche</b></div>
				<div class="mf__field" ><label for="mf_lngth" >'.__('Length','woocommerce').'</label> <input type="button" value="-" class="mf__plmn" /><input type="number" id="mf_lngth" name="mf[lngth]" value="'.$mf['lngth'].'" min="1" max="100" /><input type="button" value="+" class="mf__plmn" /> m</div>
				<div class="mf__field" ><label for="mf_wdth" >'.__('Width','woocommerce').'</label> <input type="button" value="-" class="mf__plmn" /><input type="number" id="mf_wdth" name="mf[wdth]" value="'.$mf['wdth'].'" min="1" max="100" /><input type="button" value="+" class="mf__plmn" /> m</div>
			';

			// attributes
			if(count($par)>0) {
				$html.= '<div class="mf__field mf__field--dbl" ><b>Angaben zu Artikel</b></div>';
				foreach($par as $k=>$v) {
						$html.= '<div class="mf__field" ><label for="mf_'.$k.'" >'.$v['title'].'</label> <select id="mf_'.$k.'" name="mf['.$k.']" >';
						unset($v['title']);
						foreach($v as $vi) {
							$vi->slug = strtolower( preg_replace("/[^A-Za-z0-9]/", "",$vi->name) );
							$html.=  '<option value="'.$vi->slug.'" '.(($mf[$k] == $vi->slug)?' selected="selected" ':'').' >'.$vi->name.'</option>';
						}
						$html.= '</select></div>';
				}
			} else {}

			// button <input type="submit" value="PHP '.__('Calculate','woocommerce').'" /> 
			$html.= '<div class="mf__field mf__submit" ><input type="submit" id="mf_calc" value="'.__('Berechnung','woocommerce').'" /></div>';

		$html.='</form>';

		// result for php (decide do not use this yet, may be later with ajax)
		if($re) {
			$html.= '
				<div class="za-tabcalc__result" >
					<h4 class="za-tabcalc__title" >'.$re['var']['product_title'].'</h4>
					<div class="za-tabcalc__row" > Fläche: '.($re['need']/10000).' m<sup>2</sup></div>
					<div class="za-tabcalc__row" > Menge: '.$re['count'].' St.</div>
					<div class="za-tabcalc__row" > Gesamtpreis: '.$re['price'].' '.get_woocommerce_currency_symbol().'</div>
					<div class="za-tabcalc__row" ><small> * &#150; Angaben ohne Gewähr </small></div>
					'.(($re['count'] < $re['var']['qty'])?'<form class="za-tabcalc__row za-tabcalc__row--buy" action="" method="post" ><input type="hidden" name="quantity" value="'.$re['count'].'" /><input type="hidden" name="add-to-cart" value="'.$re['var']['product_id'].'" /><input type="hidden" name="product_id" value="'.$re['var']['product_id'].'" /><input type="hidden" name="variation_id" value="'.$re['var']['variation_id'].'" /><input type="submit" id="mf_calc" value="Zum Warenkorb" /></form>':'').'
				</div>
			';
			// form buy
		} else {}

		// wpcf7
		$re = $wpdb->get_results('SELECT id FROM '.$wpdb->posts.' WHERE post_title LIKE \'tabcalc\' and post_type LIKE \'wpcf7%\' ');
		$html.= '<div class="za-tabcalc__wpcf7" style=" display: '.(($re && $var['qty'] > $re['count'])?'block':'none').'; " >'.do_shortcode('[contact-form-7 id="'.$re->ID.'" title="tabcalc"]').'</div>';

		// variations and strings as JSON (translation later) // + wp-rocket lazyload problem 
		$html.= '<script> 
			var za_tabcalc_var = JSON.parse(\''.json_encode($var).'\'); 
			var za_tabcalc_lng = JSON.parse(\''.json_encode(array(
				'currency'=>get_woocommerce_currency_symbol(),
				'square'=>'Fläche',
				'count'=>'Menge',
				'st'=>'St.',
				'price'=>'Gesamtpreis',
				'add to cart'=>'Zum Warenkorb'
			)).'\'); 
			var za_tc = false; jQuery(document).ready(function() { za_tc = new zTabcalc(); }); 
		</script>';

	return $html;
	}

	/////////////////////////////// 
	public function doTab() {
	global $product, $_REQUEST, $wpdb;
		$do = false;
		$html = '';

		if($product->product_type == 'variable' || $product->has_dimensions()) {

			// default values
			$mf = array('lngth'=>1,'wdth'=>1);
			foreach($p as $k=>$v) { $mf[$k] = ''; }

			if(isset($_REQUEST)&&isset($_REQUEST['mf'])&&is_array($_REQUEST['mf'])) {
				$mf = array_merge($mf,$_REQUEST['mf']);
				$do = true;
			} else {}
		
			// collect params/attributes -> send it to form
			if($product->product_type == 'variable') {
				$par = array('pa_auswahl-der-sortierung'=>array(),'pa_laenge'=>array());
				foreach($par as $k=>$v) {
					$par[$k] = get_the_terms( $product->id, $k);
					if(count($par[$k])>1) {
						$par[$k]['title'] = get_taxonomy($par[$k][0]->taxonomy)->labels->singular_name;
					} else {
						unset($par[$k]);
					}
				}
			} else { }

			// collect product variations -> send it to form
			$var = array();
			if($product->product_type == 'variable') {
				$tmp = $product->get_available_variations();
				for($i=0;$i<count($tmp);$i++) {
					$nm = $tmp[$i]['attributes']['attribute_pa_auswahl-der-sortierung'].$tmp[$i]['attributes']['attribute_pa_laenge'];
					$nm = strtolower( preg_replace("/[^A-Za-z0-9]/", "",$nm) );
					$var[ $nm ] = array(
						'price'=>(string) $tmp[$i]['display_price'],
						'dimensions'=>array_merge($tmp[$i]['dimensions'],array('weight'=>$tmp[$i]['weight'])),
						'qty'=> (string) $tmp[$i]['max_qty'],
						'variation_id'=>$tmp[$i]['variation_id'],
						'product_id'=>$product->id,
						'product_title'=>$product->get_title()
					);
				}
			} elseif($product->has_dimensions()) {
				$var = array('default'=>array( 
					'price'=>(string) $product->get_price(),
					'dimensions'=>array_merge($product->get_dimensions(false),array('weight'=>$product->get_weight())),
					'qty'=>(string) $product->get_max_purchase_quantity(),
					'variation_id'=>0,
					'product_id'=>$product->id,
					'product_title'=>$product->get_title()
				));
			} else { }

			// parse result -> send it to form
			$re = false;
			if($do&&$mf['lngth']>0&&$mf['wdth']>0) {
				$tmpk = ''; 
				foreach($par as $k=>$v) { $tmpk.= $mf[$k]; }
				if(isset($var[ $tmpk ])) {
					$re = array();
					$re['need'] = $mf['lngth'] * 100 * $mf['wdth'] * 100; // to cm2
					$re['every'] = $var[ $tmpk ]['dimensions']['length'] * $var[ $tmpk ]['dimensions']['width'];

					$re['count'] = ceil( $re['need'] / $re['every'] );
					$re['price'] = $re['count'] * $var[ $tmpk ]['price'];

					$re['var'] = $var[ $tmpk ];
				} else { }
			} else { }
			$html.= $this->doHTML($par,$var,$mf,$re);

		} else {}

		
		echo($html);
	return $html;
	}

	public function addTab($t) {
		// move other tabs
		$i = 20;
		foreach($t as $k=>$v) { $t[$k]['priority'] = $i; $i += 10; }

		// add first
		$t['za_tabcalc'] = array(
			'title'    => 'Materialrechner',
			'callback' => array($this,'doTab'),
			'priority' => 10
		);
	return $t;
	}

	public function addAnch() {
		echo('<a href="#tab-za_tabcalc" class="button za-tabcalc__linkto" >Berechnen Sie die benötigte Menge</a>');
	}
	
	/////////////////////////////// 
	public function install() {
	global $wpdb;
		// check dependencies
		if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
			deactivate_plugins( ZA_BLD_BASENAME, true );
			die('No WPCF7 plugin!');
		} else {}

		// create new post with type wpcf_.. and title tabcalc
		$re = $wpdb->get_results('SELECT id FROM '.$wpdb->posts.' WHERE post_title LIKE \'tabcalc\' and post_type LIKE \'wpcf7%\' ');
		if(count($re)<1) {
			$metas = array();
			$tmp = scandir(ZA_BLD_DIR.'/wpcf7/');
			$ix = count($tmp);
			for($i=0;$i<$ix;$i++) {
				if(substr($tmp[$i],0,1)=='_') {
					$k = str_replace('.txt','',$tmp[$i]);
					$metas[$k] = file_get_contents(ZA_BLD_DIR.'/wpcf7/'.$tmp[$i]);
					if($k!='_form' && strpos($metas[$k],'{')!==false) {
						$metas[$k] = unserialize($metas[$k]);
					} else {}
				} else {}
			}

			wp_insert_post(array(
				'post_content'=>file_get_contents(ZA_BLD_DIR.'/wpcf7/post_content.txt'),
				'post_title'=>'tabcalc',
				'post_status'=>'publish',
				'post_type'=>'wpcf7_contact_form',
				'comment_status'=>'closed',
				'ping_status'=>'closed',
				'post_name'=>'tabcalc',
				'meta_input'=>$metas
			));
		} else {}

	}
	
	public function uninstall() {
		// nothing
		// can delete post, but can not decide why should do that
	}

	public static function addStyle() {
		wp_enqueue_style('za-woo-tabcalc', plugins_url( 'za-woo-tabcalc/za-woo-tabcalc.css', ZA_BLD_DIR), array(), ZA_BLD_VERSION );
		wp_enqueue_script('za-woo-tabcalc', plugins_url( 'za-woo-tabcalc/za-woo-tabcalc.js',ZA_BLD_DIR), array(), ZA_BLD_VERSION, true );	
	}
	/////////////////////////////// 
	// ini
	public function ini() {
		// if(is_product()) {
		add_action('wp_print_styles', array($this,'addStyle'));
		add_filter('woocommerce_product_tabs', array($this,'addTab'));

		add_action('woocommerce_before_add_to_cart_form', array($this,'addAnch'));
	}
	
	function __construct($za,$a=false,$n=false) {
		// it is for compatibility with my cms zagata, embryo 
		$this->za = $za;
		$this->n = (($n)?$n:'tabcalc');
	}
  }

 ?>