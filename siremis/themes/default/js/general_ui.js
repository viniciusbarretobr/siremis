// JavaScript Document
function show_submenu(obj){
	var menu_links = $$('.toplevel li a');
	for(i=0;i<menu_links.length;i++) {	
		if(menu_links[i].className=='current'){
			menu_links[i].className='';			
		}
	}
	var submenus = $$('.secondlevel');
	for(i=0;i<submenus.length;i++) {	
	      if(submenus[i].style.display=='block'){
			  submenus[i].hide(); 
		  }
	}
	obj.className='current';
	var current_submenu ;
	current_submenu = obj.nextSiblings();	
	current_submenu[0].style.display="block";
	current_submenu[0].fade({duration: 0.5,from: 0.5, to: 1}); 
}

function switch_help_content(){
	var c = new Cookies();
	if($('switch_help_content_btn').className=='btn_min'){
		control_help_content("hide");
		c.set('help_form','hidden');
	}else{
		control_help_content("show");
		c.set('help_form','shown');
	}
}

function init_help_content(){
	var c = new Cookies();	
	status = c.get('help_form');
	switch(status){		
		case 'shown':
			control_help_content("show");
			break;
		case 'hidden':
		default:
			control_help_content("hide");
			break;
	
	}
}

function control_help_content(action){
	switch(action){
		case "hide":
			$('help_content').hide();
			$('switch_help_content_btn').className='btn_max';			
			break;
		case "show":
			default:
			$('help_content').style.display="block";
			$('help_content').fade({duration: 0.5,from: 0.5, to: 1}); 
			$('switch_help_content_btn').className='btn_min';			
			break;
	}
}

window.onload=fade_loader;

function fade_loader(){
	window.setTimeout("$('main_loader').fade( {from: 0.7, to: 0});",800);
	window.setTimeout("$('main_loader_bg').fade( {from: 0.7, to: 0});",800);	
	window.setTimeout("$('main_loader').hide();",1000);
	window.setTimeout("$('main_loader_bg').hide();",1000);	
	window.setTimeout("$('main_loader_bg').style.height='0px';",1500);	
}

function toggleDisplay(id){
	if($(id).style.display=='none'){
		$(id).show();
		$(id+"_toggler").className='shrink';
	}else{
		$(id).hide();
		$(id+"_toggler").className='expand';		
	}	
}
