(function() {
	tinymce.create('tinymce.plugins.bspamplugin', {
		init : function(ed, url) {
			ed.addButton('bspam', {
				title : 'Insert protected email address',
				image : url+'/../img/code_at.png',
				onclick : function() {
					var mail = prompt("BetterAntiSpamBot\nType your email address", "");
					if (mail != null && mail != 'undefined' && mail != ''){
						ed.focus(); 
ed.selection.setContent('[bspam mail="'+mail+'"]' + ed.selection.getContent() + '[/bspam]');
					}
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : "BSPAM Shortcode",
				author : 'noneevr2',
				authorurl : 'http://noneevr2.com/',
				infourl : 'http://wordpress.org/extend/plugins/betterantispam/',
				version : "1.0.0"
			};
		}
	});
	tinymce.PluginManager.add('bspam', tinymce.plugins.bspamplugin);
})();