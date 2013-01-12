jQuery(document).ready(function() {
	// Pickers
    jQuery('#gateways').find('select.pgi-picker').symphonyPickable({
        content: '#gateways',
        pickables: '.pgi-pickable'
	});
});