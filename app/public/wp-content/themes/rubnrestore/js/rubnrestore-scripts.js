// JavaScript Document

//Open the accordion on the Knowledge Hub menu when a child item is active

	jQuery(document).ready(function(){
	
		jQuery('#menu-knowledge-hub-categories .sub-menu .current-menu-item').parents().find('.current-menu-parent a').trigger('click');
		jQuery('#menu-knowledge-hub-categories .current-menu-item.menu-item-has-children').parents().find(' #menu-knowledge-hub-categories .current-menu-item.menu-item-has-children .pp-has-submenu-container > a').trigger('click');
		
	 })
