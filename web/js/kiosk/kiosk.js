if ( LI === undefined )
  var LI = {};
if ( LI.kiosk === undefined )
  LI.kiosk = [];

LI.kiosk.cart = [];
LI.kiosk.utils = [];
LI.kiosk.mustache = [];
LI.kiosk.manifestations = {};
LI.kiosk.cart.lines = {};

$(document).ready(function(){
	LI.kiosk.initPlugins();
  	LI.kiosk.getManifestations();
});

LI.kiosk.getManifestations = function(){
	LI.kiosk.utils.showLoader();

	$.ajax({
    url: '/tck_dev.php/transaction/getManifestations/action',
    type: 'GET',
    data: { simplified: 1 },
    dataType: 'json',
    success: LI.kiosk.insertManifestations,
    error: LI.kiosk.utils.error
  });
};

LI.kiosk.insertManifestations = function(data){

	var type = 'manifestations';
	var cardTemplate = $('#manif-card-template').html();

	$.each(data.success.success_fields[type].data.content, function(key, manif){
		
		if ( window.location.hash == '#debug' )
			console.error('Loading an item (#' + manif.id + ') from the ' + type);

		//re arrange properties
		manif.name = manif.name == null ? manif.category : manif.name;
		var manifDate = new Date(manif.happens_at.replace(' ', 'T'));
		manif.start = manifDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
		var endDate = new Date(manif.ends_at);
		manif.end = endDate.getHours() + ':' + endDate.getMinutes();

		if(manif.image_id != undefined)
			manif.background = 'background-image: url("' + manif.image_url + '")' ;
		else
			manif.background = 'background-color: ' + manif.color;

		$.each(manif.gauges, function(i, gauge){
			if( gauge.name == 'INDIVIDUELS' ){

				manif.prices = {};

				$.each(gauge.available_prices, function(key, price){

					if( price.color == undefined )
						price.color = '#4FC3F7';

					manif.prices[price.id] = price;
				});
			}
		});

		//add manif to global variable for future retrieval
		LI.kiosk.manifestations[manif.id] = manif;

		//Render and insert card
  		$('#manifs-list').append(Mustache.render(cardTemplate, { manif: manif }));
	});

	LI.kiosk.addManifListener();
	LI.kiosk.utils.hideLoader();
};

LI.kiosk.utils.error = function(error){
	console.error(error);
	LI.kiosk.utils.hideLoader();
};

LI.kiosk.utils.showLoader = function(){
	$('#spinner').addClass('is-active');
	$('#spinner').css('display', 'block');
};

LI.kiosk.utils.hideLoader = function(){
	$('#spinner').removeClass('is-active');
	$('#spinner').css('display', 'none');
};

LI.kiosk.utils.flash = function(selector){
	Waves.attach(selector);
	Waves.init();
	Waves.ripple(selector);
};

LI.kiosk.addManifListener = function(){
	$('#manifs-list').on('click', '.manif', function(event){
		var manif = LI.kiosk.manifestations[$(event.currentTarget.children).attr('id')];
	  	LI.kiosk.showDetails(manif, $(this));
	});
}

LI.kiosk.mustache.cacheTemplates = function(){
	var templates = ['manif-card-template'];
	//make mustache cache the templates for quicker future uses
	$('script[type="x-tmpl-mustache"]').each(function(id, template){
		Mustache.parse($(template).html());
	});
};

LI.kiosk.showDetails = function(manif, card){
	var detailsTemplate = $('#manif-details-template').html();
	
	// insert manif info
	$('#manif-details-card').html(Mustache.render(detailsTemplate, { manif: manif }));
	// insert prices
	LI.kiosk.insertPrices(manif);
	//show details panel
	LI.kiosk.utils.switchPanels();
};

LI.kiosk.insertPrices = function(manif){
	var priceTemplate = $('#price-card-template').html();

	for(key in manif.prices)
		$('#manif-details-card #prices').append(Mustache.render(priceTemplate, { price: manif.prices[key] }));

	LI.kiosk.addPriceListener(manif);
};

LI.kiosk.addPriceListener = function(manif){
	$('#prices').on('click', '.price', function(event){
		LI.kiosk.cart.addItem(manif, manif.prices[$(event.currentTarget.children).attr('id')])
	});
};

LI.kiosk.initPlugins = function(){
	Waves.attach('.waves-effect');
	Waves.init();
	LI.kiosk.mustache.cacheTemplates();
};

LI.kiosk.cart.addItem = function(item, price){

	var newLine;
	var exists = false;

	$.each(LI.kiosk.cart.lines, function(key, line){
		
		if(line.name == item.name && line.price.id == price.id){
			var htmlLine = $('#' + line.id);
			line.qty++;
			LI.kiosk.cart.lineTotal(line);
			exists = true;
			htmlLine.find('.line-total').text(line.total);
			htmlLine.find('.line-qty').text(line.qty);
			LI.kiosk.utils.flash('#' + line.id);
			
		}
	});

	if(!exists){
		var newLine = {
			id: LI.kiosk.utils.generateUUID(),
			name: item.name,
			price: price,
			qty: 1,
			total: price.value
		};

		LI.kiosk.cart.lines[newLine.id] = newLine;
		LI.kiosk.cart.insertLine(LI.kiosk.cart.lines[newLine.id]);
		LI.kiosk.utils.flash('#' + newLine.id);
	}

	$('#cart').show(500);
	$('#cart').css('display', 'flex');
	LI.kiosk.cart.cartTotal();
};

LI.kiosk.cart.removeItem = function(lineId) {
	var line = LI.kiosk.cart.lines[lineId];
	var htmlLine = $('#' + lineId);

	line.qty--;
	LI.kiosk.cart.lineTotal(line);

	if(line.qty == 0){
		delete LI.kiosk.cart.lines[lineId]
		LI.kiosk.cart.removeLine(htmlLine);
	}
	else{
		LI.kiosk.cart.lines[lineId] = line;
		htmlLine.find('.line-qty').text(line.qty);
		htmlLine.find('.line-total').text(line.total);
	}

	LI.kiosk.cart.cartTotal();

	if(Object.keys(LI.kiosk.cart.lines) < 1)
		$('#cart').hide(200);
};

LI.kiosk.utils.generateUUID = function(){

    var d = new Date().getTime();
    //Force letter as first character to avoid selector issues
    var uuid = 'Axxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
    });

    return uuid.toUpperCase();
};

LI.kiosk.utils.switchPanels = function(){
	$('#manifs').effect('slide', {
		direction: 'right', 
		mode: 'hide', 
		duration: '500',
		complete: function(){
			//$('#manif-details-card').show();
			//$('#spacer').show();
			// $('#manifs').css('margin-right', '-2000px');
			//$('#manif-details-card').css('margin-left', 0);
			$('#manif-details-card').effect('slide', {
		direction: 'left', 
		mode: 'show',
		duration: '500'
	});
		}
	});
	
	
	

	$('#back-fab').unbind('click').click(function(){
		$('#manif-details-card').effect('slide', {
			direction: 'left', 
			mode: 'hide', 
			duration: '500',
			complete: function(){
				$('#manifs').effect('slide', {
			direction: 'right', 
			mode: 'show', 
			duration: '500',
			complete: function(){
				//$('#spacer').hide();
			}
		});
			//	$('#manifs').show();
				//$('spacer').show();
				//$('#manif-details-card').css('margin-left', '-5000px');
				// $('#manifs').css('margin-right', '0');
			}
		});
		
		
		

		$(this).hide();
	}).show();
}

LI.kiosk.cart.insertLine = function(line){
	var lineTemplate = $('#cart-line-template').html();
	$('#cart-lines').append(Mustache.render(lineTemplate, { line: line }));
	$('#' + line.id + ' .remove-item').click(function(){
		LI.kiosk.cart.removeItem(line.id);
	});
};

LI.kiosk.cart.removeLine = function(htmlLine){
	$(htmlLine).hide(500).remove();
};

LI.kiosk.cart.lineTotal = function(line){
	
	line.total = LI.format_currency(line.price.raw_value * line.qty, false);
};

LI.kiosk.cart.cartTotal = function(){
	var total = 0;

	$.each(LI.kiosk.cart.lines, function(key, line){
		total += parseFloat(line.total);
	});

	$('#cart-total-value').text(LI.format_currency(total, false));
};