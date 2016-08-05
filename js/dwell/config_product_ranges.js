var DwellConfigProdRange = {
	getFormattedPriceRange: function(price_array) {

		return this.getPriceRange('regular',price_array);
	},
	getFormattedSpecialPriceRange: function(price_array) {
		return this.getPriceRange('old',price_array);
	},
	getPriceRange: function(price_type,price_array1) {
        
		if (typeof spConfig == 'undefined') return false;
		var basePrice = parseFloat( (price_type=='old') ? spConfig.config.oldPrice : spConfig.config.basePrice );
		var price_array = [];
		var converted_base_price = 0;
		var converted_end_price = 0;
		var price_string = '';
		var attribute_ids = this.getAttributeIds();
		for (a=0;a<attribute_ids.length;a++) {
			for (o=0;o<spConfig.config.attributes[attribute_ids[a]].options.length;o++) {
				price_array.push(parseFloat( (price_type=='old') ? spConfig.config.attributes[attribute_ids[a]].options[o].oldPrice : spConfig.config.attributes[attribute_ids[a]].options[o].price));
			}
		}
		price_array1.sort(function(a,b){return a - b});
		
		converted_base_price = (price_array1[0]).toFixed(2);
		converted_end_price  = (typeof price_array1[1] != 'undefined')?((price_array1[1]).toFixed(2)): 'NaN';
	
		price_string = (converted_base_price == converted_end_price || converted_end_price == 'NaN') ?  this.convertUSD(converted_base_price): this.convertUSD(converted_base_price)+'-'+ this.convertUSD(converted_end_price);
		price = '<span class="price"';
		if (registry_enabled) {
		  price += ' id="registry-product-price"';
		}
		price += '>' + price_string + '</span>';
		return price;
	},
	getAttributeIds: function() {
		var ids = [];
		jQuery('.super-attribute-select').each(function() {
			ids.push(parseInt(jQuery(this).attr('id').substring(9)));
		});
		return ids;
	},
	convertUSD: function(n) {
		var n = n.toString(), 
		d = n.split('.')[0], 
		c = (n.split('.')[1] || '') +'00';
		d = d.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1,')
			.split('').reverse().join('');
		return '$' + d + '.' + c.slice(0, 2);
	}
}
