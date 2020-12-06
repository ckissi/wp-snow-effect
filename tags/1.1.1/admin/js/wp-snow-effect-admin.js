(function( $ ) {
	'use strict';

	$( window ).load(function() {
        var previous;
        $('#settings_flake_type').focus(function () {
                previous = this.value;
        }).change(function() {
            if(this.value < 100) {
                alert("Sorry...\n\nThis Character Type is available in PRO version only.\n\nPlease consider to upgrade.");
                this.value = previous;
            }
		});
	});

})( jQuery );
