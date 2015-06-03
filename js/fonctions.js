//Vartiables globales
var id_ticket_attente = '';
var id_ba_attente = '';

$(window).load(function(){
	resize_all();
	set_focus();
	
	/*
	 * EVENEMENTS SOURIS
	 */
	
	// Clavier numérique
	$("#chiffres :button, #moin").click(function(){
		add(this.value);
	});
	
	$("#effacer").click(function(){
		del();
	});
	
	$("#vider").click(function(){
		vider();
	});
	
	// Remise produit, quantite
	$("#actions_remise_pd :button").click(function(){
		add_propertie(this);
	});
	
	// Remises globales
	$("#actions_remise_vt :button").click(function(){
		add_remise_globale($("#texte").val());
	});
	
	// Mettre en attente, annulation
	$("#actions_vt :button").click(function(){
		modif_vente(this);
	});
	
	// Changement du type de ticket à recherché : en attente ou archivé
	$(".ui-radio").click(function(){
		changement_liste_ticket($(this).find('input').val());
		if($(this).find('input').val() == 1){ //en attente
			$('#reprendre_ticket').show();
			$('#supprimer_ticket').show();
			$('#imprimer_ticket').hide();
			$('#imprimer_ticket_sp').hide();
			$('#bonachat_ticket').hide();
			$('#send_bill_mail').hide();
		}
		else if($(this).find('input').val() == 2){ //archivés
			$('#reprendre_ticket').hide();
			$('#supprimer_ticket').hide();
			$('#imprimer_ticket').show();
			$('#imprimer_ticket_sp').show();
			$('#bonachat_ticket').show();
			$('#send_bill_mail').show();
		}
	});
	
	// Click sur le bouton "Reprendre Ticket"
	$('#reprendre_ticket').click(function(){
		if(id_ticket_attente != ''){
			reprendre_vente(id_ticket_attente);
			id_ticket_attente = '';
		}
	});
	
	// Click sur le bouton "Supprimer Ticket"
	$('#supprimer_ticket').click(function(){
		if(id_ticket_attente != ''){
			id_facture_supprimer = id_ticket_attente;
			annuler_facture(id_facture_supprimer);
			$('#'+id_facture_supprimer).parent().parent().parent().remove();
			load_ticket($('#id_facture').val());
			id_ticket_attente = '';	
		}
	});
	
	// Click sur le bouton "Imprimer Ticket"
	$('#imprimer_ticket').click(function(){
		if(id_ticket_attente != ''){
			id_facture = id_ticket_attente;
			imprimer_ticket(id_facture,1,1,0,0);
			id_ticket_attente = '';
		}
	});
	
	// Click sur le bouton "Imprimer Ticket Sans Prix"
	$('#imprimer_ticket_sp').click(function(){
		if(id_ticket_attente != ''){
			id_facture = id_ticket_attente;
			imprimer_ticket(id_facture,1,1,0,1);
			id_ticket_attente = '';
		}
	});
	
	// Click sur le bouton "Nouveau Client"
	$('#nouveau_client').click(function(){
		$('#radio-choice-'+$('#fiche-client-civilite').val()).checkboxradio( "refresh" );
		$('#radio-choice-MME').click().checkboxradio( "refresh" ).click();
		$('#id_client').val('');
		$('#id_contact').val('');
		$('#block_valider, #block_annuler, #block_validerandassoc').show();
		$('#block_nouveau').hide();
		$('#contener_client, #erreur').hide();
		$('#form-client').show();
		$('#form-client #textinput-nom').focus();
	});
	
	// Click sur un mode de paiement
	$('#types_paiement :button').click(function(){
		if(($("#ticket-lignes li").attr('data-wrapperels') != undefined) && $(this).attr('data') != "ba" )
			validation_vente($(this).attr('data'));
			
		vider();
		set_focus();
	});
	
	// Ensemble des évènements clavier se produisant sur la page
	$("#page").keydown(function(event){
		set_focus();
	});
	
	// Ensemble des évènements clavier se produisant sur la page
	$("#main_right").keydown(function(event){
		if(event.which == 13){ //appui sur la touche entrée
			code = $('#texte').val();
			vider();
			rechercher_produit(code);
		}
	});
	
	// Ensemble des évènements clicks se produisant sur la page
	$("#page").click(function(event){
		set_focus();
	});
	
	// Recherche ville
	$('#form-client #textinput-cp, #form-client #textinput-ville').autocomplete({
		source: function(request, response) {
			rechercher_ville(request, response);
		}
		,minLength: 5
		,messages: {
			noResults: '',
			results: function() {}
		}
	});
	
	//changement du vendeur
	/*$('#select-choice-a').change(function(){
		changeVendeur($(this).val(),$('#id_facture').val());
	});*/
	
	$("#recap_vendeur").on( "change", function(event, ui) {
 		changeVendeur($("#recap_vendeur").val(),$('#id_facture').val());
	});
	
	creer_facture();
});

// Affichage du formulaire de création d'un bon d'achat
function showCreateBonachat(type){
	if(type == "avoir"){
		if(id_ba_attente != '' || id_ticket_attente != ''){
			if(id_ticket_attente != '')
				id_ba_attente = id_ticket_attente;
			switch_onglet("onglet5");
			$('#id_facture_bonachat').val(id_ba_attente);
			getFacture(id_ba_attente);
			$('#ref_ticket, #retour_bonachat, #valider_bonachat').show();
			$('#use_bonachat, #creer_bonachat, #supprimer_bonachat, #imprimer_bonachat, #valider_paiement').hide();
			id_ba_attente = '';
		}
	}
	else if(type == "cadeau"){
		$('#id_facture_bonachat').val('');
		$('#id_client_bonachat').val($('#recap_client').attr('value'));
		$('#textinput-client').val($('#recap_client').text());
		$('#textinput-montant').val('').focus();
		$('#retour_bonachat, #valider_paiement').show();
		$('#ref_ticket, #use_bonachat, #creer_bonachat, #supprimer_bonachat, #imprimer_bonachat, #valider_bonachat').hide();
	}
	
	$('#contener_bonachat').hide();
	$('#form-bonachat').show();
}

// Click sur le bouton "Retour" du formulaire des bons d'achat
function backBonachat(){
	$('#form-bonachat, #retour_bonachat, #valider_bonachat, #valider_paiement').hide();
	$('#contener_bonachat, #use_bonachat, #creer_bonachat, #supprimer_bonachat, #imprimer_bonachat').show();
	refreshBonAchat();

}

function scrollToBottom(){
	$('#ticket').scrollTop($('#ticket').get(0).scrollHeight );	
}

// Click sur un ticket en attente ou archivé
function event_click_liste(bouton){
	load_ticket($(bouton).attr('id'),'ticket-archive-lignes');
	id_ticket_attente = $(bouton).attr('id');
	$('#liste_tickets .ui-link-inherit').removeClass('select');
	$(bouton).addClass('select');
}

// Click sur un ticket en attente ou archivé
function event_click_liste_ba(bouton){
	id_ba_attente = $(bouton).attr('id');
	$('#liste_bonachat .ui-link-inherit').removeClass('select');
	$('#liste_bonachat .ui-link-inherit').css('color','white');
	$(bouton).addClass('select');
	
	$(bouton).css('color','#497BAE');
	
	
}

// Positionne le focus
function set_focus(){
	
	if($('#popupUseRewards').parent().hasClass('ui-popup-active')) { //popin fidélité
		null;		
	}
	else{
		$(".texte").focus();	
	}
}

// Ajoute un nombre dans le champ focus 	
function add(num){
	$('#texte').val($('#texte').val()+num);
	set_focus();
}

//Efface le dernier caractère du champ
function del(){
	$('#texte').val($('#texte').val().substr(0,$('#texte').val().length -1));
	set_focus();
}

// Vide le champ focus
function vider(){
	$(".texte").val('');
}

// Réinitialisation de la vente
function vider_all(){
	vider_produit();
	$("#totaux label, #etat_payement label").attr("value","0").empty().append("0");
	vider();
	$("#ticket-lignes").empty();
}

// Réinitialisation du produit
function vider_produit(){
	$("#recap_quantite").attr("value","1").empty().append("1");
	$("#recap_remise, #recap_remise_mt, #recap_prix").attr("value","0").empty().append("0");
	vider();
}

// Ajout d'une propriété à la vente (quantité, remise, etc)
function add_propertie(element){
	
	set_focus();
	
	if($("#texte").val()=='') return false;
	
	val = parseFloat($("#texte").val());
	
	if(element.id != "quantite"){
		if(element.id == "remise_mt"){
			ajout_remise(val);
		}
		else if(element.id == "remise" || element.id == "total_remise"){
			if(val <= 100){
				$("#recap_"+element.id).attr("value",val);
				$("#recap_"+element.id).html(val);
			}
			else{
				vider();
				return false;
			} 
		}
		else{
			$("#recap_"+element.id).attr("value",val);
			$("#recap_"+element.id).html(val);
		}
	}
	else if(element.id == "quantite" && val!=0){
		$("#recap_"+element.id).attr("value",val);
		$("#recap_"+element.id).html(val);
	}	
	
	$("#recap_"+element.id).css({fontSize:'24px', left:'10px', position:'relative', color:'#66ff66'} ).animate({
		fontSize:'18px'
		, left:'0px'
		}
		, {
		complete : function() {
			$(this).css({color:'#fff'} );	
		} 
	});
	
	vider();
	
	return true;
}

// Mise en attente ou annulation de la vente
function modif_vente(element){
	if(element.id == "attente"){
		if(confirm("Mettre en attente la vente en cours?")){
			vider_all();
			creer_facture();
		}
	}
	else if(element.id == "annuler"){
		if(confirm("Supprimer définitivement la vente en cours?")){
			annuler_facture($("#id_facture").val());
			vider_all();
			creer_facture();
		}
	}
}

// Reprise d'une vente en attente
function reprendre_vente(id_facture){
	if(id_facture != undefined)
	{
		$('#id_facture').val(id_facture);
		load_ticket(id_facture);
		
		switch_onglet('onglet1');
		vider_all();
	}
}

// Redimentions au chargement de la page
function resize_all(){
	$('#main_left').width($('#main').width()-$('#main_right').width()); //largeur colonne de gauche
	$('#ticket').height($('#main').height()-$('#zone_saisie').height()-$('#clavier').height());
}

// Actions graphique au changement d'onglet
function switch_onglet(onglet){
	
	$("#onglet1,#onglet2,#onglet3,#onglet4,#onglet5").removeClass("ui-btn-active");
	$("#"+onglet).addClass("ui-btn-active");	
	
	switch(onglet)
	{
		case "onglet1": //Ticket
			$("#corps-2, #corps-3, #actions_bonachat, #actions_tickets, #actions_clients, #ticket-archive, #back-to-onglet1").hide();
			$("#corps-1, #zone_saisie, #clavier,#ticket").show();
			$('#ticket').height($('#main').height()-$('#zone_saisie').height()-$('#clavier').height());
			set_focus();
			break;
		case "onglet2": //Tickets en attente et archivés
			init_liste($('#liste_tickets'),'%%%','ticket');
			$("#corps-1, #corps-3, #corps-4, #clavier, #zone_saisie, #actions_clients, #actions_bonachat, #ticket").hide();
			$("#corps-2, #actions_tickets, #ticket-archive, #back-to-onglet1").show();
			$('#ticket-archive').height($('#main').height()-$('#actions_tickets').height());
			$("#corps-2 .ui-input-text").val('').focus();
			break;
		case "onglet3": //Produits
			$("#corps-1, #corps-2, #corps-4, #zone_saisie, #clavier, #actions_tickets, #actions_clients, #actions_bonachat, #ticket-archive").hide();
			$("#corps-3,#ticket, #back-to-onglet1").show();
			$('#ticket').height($('#main').height());
			$("#corps-3 .ui-input-text").val('').focus();
			break;
		case "onglet4": //Clients
			$("#corps-1, #corps-2, #corps-3, #zone_saisie, #clavier, #actions_tickets, #actions_bonachat, #ticket-archive, #form-client, #block_annuler, #block_valider, #block_validerandassoc,#block_fidelite").hide();
			$("#corps-4, #actions_clients, #contener_client, #block_nouveau,#ticket, #back-to-onglet1").show();
			$('#ticket').height($('#main').height()-$('#actions_clients').height());
			$("#corps-4 .ui-input-text").val('').focus();
			break;
		case "onglet5"://bon d'achat'
			$("#corps-1, #corps-2, #corps-3, #corps-4, #zone_saisie, #clavier, #actions_tickets, #actions_clients, #ticket-archive").hide();
			$("#corps-bonachat, #actions_bonachat,#ticket, #back-to-onglet1").show();
			$('#ticket').height($('#main').height()-$('#actions_bonachat').height());
			$("#corps-bonachat .ui-input-text").val('').focus();
			backBonachat();
			break;
	}
	
}

//Traitement du formulaire client
function traitement_client(id_button){
	$('#radio-choice-'+$('#fiche-client-civilite').val()).checkboxradio( "refresh" );
	if(id_button == "valider" || id_button == "validerandassoc"){
		formvalid = true;
		
		//Vérification des champs obligatoire
		if($('#textinput-nom').val() != '')
			nom = $('#textinput-nom').val();
		else
			formvalid = false;
		
		if($('#textinput-prenom').val() != '')
			prenom = $('#textinput-prenom').val();
		else
			formvalid = false;
		
		if($('#textinput-email').val() == '' && $('#textinput-tel').val() == '' && $('#textinput-datenaiss').val() == '')
			formvalid = false;
		
		// Tous les champs obligatoire sont remplis
		if(formvalid){
			civilite = $('#fiche-client-civilite').val();
			adresse = $('#textinput-adresse').val();
			cp = $('#textinput-cp').val();
			email = $('#textinput-email').val();
			datenaiss = $('#textinput-datenaiss').val();
			ville = $('#textinput-ville').val();
			tel = $('#textinput-tel').val();
			id = $('#id_client').val();
			id_contact = $('#id_contact').val();
			barcode = $('#textinput-barcode').val();
			
			encours_init = $('#textinput-encours-init').val();
			
			if(id == '') 
				id = 0;
			//alert(barcode);
			update_client(id, id_contact,nom,prenom,civilite,adresse,cp,email,datenaiss,ville,tel,encours_init,id_button,barcode);
			$('#id_client').val('');
		}
		else
			$('#erreur').show();
	}
	else if(id_button == "annuler"){
		$('#block_valider, #block_annuler, #block_validerandassoc,#block_fidelite').hide();
		$('#block_nouveau').show();
		$('#form-client input[type=text]').val('');
		$('#form-client').hide();
		$('#contener_client').show();
		$('#liste_clients').listview('refresh');
	}
}
