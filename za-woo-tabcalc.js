
// ザガタ ////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

function zTabcalc(za,a,n) {
	/* Zagata.TabCalc for WP / Woo */

	this.za = (typeof(za)=='undefined')?false:za; // core
	var a = (typeof(a)=='undefined')?false:a; // attr
	this.n = (typeof(n)=='undefined')?'zTabcalc':n; // name

	///////////////////////////////
	// funcs
	this.l18n = function(str) {
		str = str.toLowerCase();
	return (typeof(this.lng[str])!=='undefined')?this.lng[str]:str;
	};

	this.doHTML = function(re) {
		re = re || false;
		var html = '';
		if(re) {
			html = '<h4 class="za-tabcalc__title" >'+re.var['product_title']+'</h4><div class="za-tabcalc__row" > '+this.l18n('Square')+': '+(re.need/10000)+' m<sup>2</sup></div><div class="za-tabcalc__row" > '+this.l18n('Count')+': '+re.count+'  '+this.l18n('St')+'</div><div class="za-tabcalc__row" > '+this.l18n('Price')+': '+re.price+' '+this.l18n('currency')+' </div><div class="za-tabcalc__row" ><small> * &#150; Angaben ohne Gewähr </small></div>';

			if(re.count < re.var['qty']) {
				// there are enough products, add buy form
				html += '<form class="za-tabcalc__row za-tabcalc__row--buy" action="" method="post" ><input type="hidden" name="quantity" value="'+re.count+'" /><input type="hidden" name="add-to-cart" value="'+re.var['product_id']+'" /><input type="hidden" name="product_id" value="'+re.var['product_id']+'" /><input type="hidden" name="variation_id" value="'+re.var['variation_id']+'" /><input type="submit" id="mf_calc" value="'+this.l18n('Add to cart')+'" /></form>';
				this.wpcf7.style.display='none';
			} else {
				// there is no enough products, fill fields, hide it, show wpcf7
				jQuery('.za-tabcalc__wpcf7 input[name^=var_').attr('type','hidden');
				jQuery('.za-tabcalc__wpcf7 input[name=var_lngth]').val(this.lngth.value);
				jQuery('.za-tabcalc__wpcf7 input[name=var_wdth]').val(this.wdth.value);
				if(this.pclss) { jQuery('.za-tabcalc__wpcf7 input[name=var_pclss]').val(this.pclss.value); } else {}
				if(this.plngth) { jQuery('.za-tabcalc__wpcf7 input[name=var_plngth]').val(this.plngth.value); } else {}
				jQuery('.za-tabcalc__wpcf7 input[name=var_need]').val((re.need/10000));
				jQuery('.za-tabcalc__wpcf7 input[name=var_count]').val(re.count);
				jQuery('.za-tabcalc__wpcf7 input[name=var_price]').val(re.price+' '+this.l18n('currency'));
				this.wpcf7.style.display='block';
			}

		} else {}
		re = document.createElement('div'); re.className = 'za-tabcalc__result';
		re.innerHTML = html;
	return re;
	};

	this.doCalc = function(e) {
		e = e || window.event;
		e.preventDefault();

		var re = new Object();
		re.var = false;

		var tmp = ''; // key for variants
		if(za_tc.pclss || za_tc.plngth) {
			if(za_tc.pclss && za_tc.pclss.options.length > 1) { tmp += this.p.pclss.value; } else {}
			if(za_tc.plngth && za_tc.plngth.options.length > 1) { tmp += this.p.plngth.value; } else {}

			if(typeof(this.p.var[ tmp ]) != 'undefined') {
				re.var = this.p.var[ tmp ];
			} else {}
		} else {
			re.var = this.p.var[ Object.keys(this.p.var)[0] ];
		}

		if(re.var && this.p.lngth.value > 0 && this.p.wdth.value > 0) {
			re.need = this.p.lngth.value * this.p.wdth.value * 10000;
			re.every = re.var['dimensions']['length'] * re.var['dimensions']['width'];
			
			re.count = Math.ceil( re.need / re.every );
			re.price = re.count * re.var['price'];
			re.price = (Math.round(re.price * 10) / 10).toFixed(2);

			if(tmp = jQuery('.za-tabcalc__result')[0]) { 
				tmp.parentNode.removeChild(tmp); 
			} else {}

			this.form.parentNode.insertBefore( this.p.doHTML(re), this.p.wpcf7 );
		} else {}

		e.stopPropagation();
	};

	this.doPlusMinus = function() {
		if(this.value == '-' || this.value == '+') {
			var tmp = this.parentNode.getElementsByTagName('input')[1];
			var val = parseInt(tmp.value);
			tmp.value = (this.value=='-')?val-1:val+1;
		} else {}
	};
	
	///////////////////////////////
	// ini

	// auto switch to tab
	var hs = window.location.hash;
	if(hs) { 
		setTimeout( () => jQuery( hs.replace('tab-','tab-title-')+' a').click(), 1000); // fancy arrow func
	} else {}

	// scroll to tab and switch
	jQuery('.za-tabcalc__linkto').click(function(e) {
		e.preventDefault;
		var hash = this.href.substr(this.href.indexOf('#'))
		var dest = hash.replace('tab-','tab-title-');
		jQuery( dest+' a').click();
		jQuery('html, body').animate({scrollTop:(jQuery(dest).offset().top - 200)}, 1000);
		window.location.hash = hash;
		e.stopPropagation();
	return false;
	});

	// JSON check
	this.var = za_tabcalc_var;
	this.lng = za_tabcalc_lng;

	// bind inputs
	this.lngth = jQuery('#mf_lngth')[0];
	this.wdth = jQuery('#mf_wdth')[0];
	this.pclss = jQuery('#mf_pa_auswahl-der-sortierung')[0];
	this.plngth = jQuery('#mf_pa_laenge')[0];

	// bing plusminus 
	jQuery('.mf__field .mf__plmn').click(this.doPlusMinus);

	// bind calc button
	this.calc = jQuery('#mf_calc')[0];
	this.calc.p = this;
	this.calc.type = 'button';
	this.calc.addEventListener('click',this.doCalc);
	
	// find wpcf7 tabcalc form and feel fields
	this.wpcf7 = jQuery('.za-tabcalc__wpcf7')[0];
	jQuery('.za-tabcalc__wpcf7 input[name=title]').val( this.var[ Object.keys(this.var)[0] ].product_title ).attr('type','hidden');
	jQuery('.za-tabcalc__wpcf7 input[name=url]').val( window.location.href ).attr('type','hidden');
	this.wpcf7.style.display = 'none';

};