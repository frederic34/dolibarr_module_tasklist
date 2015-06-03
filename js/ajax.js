/*
 * APPEL DES CHARGEMENTS AJAX
 * 
 */

$(document).on( "pageinit", "#page", function() {
	
	/* CHARGEMENT DE LA LISTE DES PRODUITS */
	init_event_liste('produit',"#liste_produits");
	
	/* CHARGEMENT DE LA LISTE DES CLIENTS */
	
	init_event_liste('client',"#liste_clients");
	
	/* CHARGEMENT DE LA LISTE DES Tickets */
	init_event_liste('ticket',"#liste_tickets");
	
	/* CHARGEMENT DE LA LISTE DES BONS D'ACHAT */
	init_event_liste('bonachat',"#liste_bonachat");
	
	init_liste($('#liste_tickets'),'%%%','ticket');
	
	getCategorie(0);
	
	testLoginStatus();
	
	
	$("#checkbox-gaucher").bind( "change", function(event, ui) {
	  	if($(this).prop('checked') == true) {
	  		setViewForGaucher(1);
	  	}
	  	else{
	  		setViewForGaucher(0);
	  	}
	});
	
});

function caisse_logout() {
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "html",
		crossDomain: true,
		data: {
			   put:'logout'
		}
	})
	.then(function (data){
		
		testLoginStatus();
		
	});
	
	
}

function testLoginStatus() {
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "html",
		crossDomain: true,
		data: {
			   get:'logged-status'
		}
	})
	.then(function (data){
		
		if(data!='ok') {
			document.location.href = document.location.href; // reload car la session est expirée		
		}
		else {
			setTimeout(function() {
			      testLoginStatus();
			}, 10000);
		}
		
	});

}

// Initialise une liste de type "listview" 
function init_event_liste(type_element,id_liste)
{
	$( id_liste ).on( "listviewbeforefilter", function ( e, data ) {
		
		var $ul = $( this ),
        $input = $( data.input ),
        search = $input.val(),
        html = "";
        
        $input.attr('type_element', type_element);
        $input.attr('id_liste', id_liste);
	        
	   //alert(id_liste+', '+type_element)
	   		
   		if (CAISSE_MIN_LENGTH_TO_AUTOCOMPLETE>0 && search && search.length >= CAISSE_MIN_LENGTH_TO_AUTOCOMPLETE ) {
	    	init_liste($ul,search,type_element);
	    }
		else if (CAISSE_MIN_LENGTH_TO_AUTOCOMPLETE==0) {
	        $input.unbind().keypress(function(eInput) {
	        	
	        	if(eInput.which == 13) {
	        		init_liste($($(this).attr('id_liste')),$(this).val(),$(this).attr('type_element'));	
	        			
	        	}
	        	
	        });
		}		
	    
    });
}
function refreshBonAchat() {
	init_liste( $('#liste_bonachat') ,'%%%','bonachat');
}

function init_liste(obj,search,type_element) {
		
		if(type_element=='produit') {
			getFunction = "liste-produits";	
		}
		else if(type_element=='client') {
			getFunction = "liste-clients";	
		}
		else if(type_element=='bonachat') {
			getFunction = "liste-bonachat";	
		}
		else{
			getFunction = "liste-tickets";
		}
		
		
        obj.html( "<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>" );
        obj.listview( "refresh" );
       
        var type = 0;
        if(type_element=='ticket') {
        	type = $('input[name=type-ticket]:checked').val();
        }
       	
       	socid = $('#recap_client').attr('value');
      
        $.ajax({
            url: 'ajax/interface.php',
            dataType: "json",
            crossDomain: true,
            data: {
            	get : getFunction,
            	json:1,
                search: search,
                socid: socid,
                type: type
            }
        })
        .then( function ( response ) {
            _display_liste(type_element, response);
        });
        
    
	
}

function _display_liste(type_element, response) {
	html = "";
	if(response.length > 0){
	    $.each( response, function ( i, val ) {
	    	if(type_element == 'produit') {
	    		$ul = $("#liste_produits");
	        	html += "<li><a onclick='ajout_produit("+val.id+");'>" + val.ref +", "+ val.label +", "+ val.price_ttc + " &euro;</a></li>";
			} else if(type_element == 'client') {
				$ul = $("#liste_clients");

	        	if(val.societe){
	        		html += "<li>"+
	        				"<a onclick='affecter_client("+val.socid+",\""+val.societe+"\",\""+val.ville+"\");'>" + val.societe +", "+ val.codebarre+", "+ val.ville +"</a>"+
	        				'<a href="javascript:infos_client('+val.socid+')">Modifier client</a>'+
	        			"</li>";
	        	}
	        	else{
	        		html += "<li>"+
	        				"<a onclick='affecter_client("+val.socid+",\""+val.prenom+"\",\""+val.nom+"\");'>" + val.nom +" "+ val.prenom +", "+ val.codebarre +", "+ val.anniv +", "+ val.ville +"</a>"+
	        				'<a href="javascript:infos_client('+val.socid+')">Modifier client</a>'+
	        			"</li>";
	        	}
	        	
			}
			else if(type_element == 'ticket') {
				$ul = $("#liste_tickets");
	        	html += "<li>"+
	        				'<a href="#" id="'+ val.id +'" onclick="event_click_liste(this);">'+val.facnumber+', '+ val.date +', '+ val.nom +', '+ val.total +' &euro;</a>'+
	        			"</li>";
			}
			else if(type_element == 'bonachat') {
				$ul = $("#liste_bonachat");
	        	html += "<li>"+
	        				'<a href="#" id="'+ val.baid +'" onclick="event_click_liste_ba(this);">'+val.numero+', '+val.socnom +', '+ val.type +', '+ val.montant +' &euro;, '+val.datevalid+'</a>'+
	        			"</li>";
			}
	    });
    }
    else{
		if(type_element == 'produit') {
    		$ul = $("#liste_produits");
        	html += "<li>Aucun produit correspondant &agrave; votre recherche</li>";
		} else if(type_element == 'client') {
			$ul = $("#liste_clients");
        	html += "<li>Aucun client correspondant &agrave; votre recherche</li>";
		}
		else if(type_element == 'ticket') {
			$ul = $("#liste_tickets");
        	html += "<li>Aucun ticket correspondant &agrave; votre recherche</li>";
		}
		else if(type_element == 'bonachat') {
			$ul = $("#liste_bonachat");
        	html += "<li>Aucun bon d'achat correspondant &agrave; votre recherche</li>";
		}
	}
    $ul.html( html );
    $ul.listview( "refresh" );
    $ul.trigger( "updatelayout");
}

// Recherche si un produit est présent dans dolibarr
function rechercher_produit(code){
	socid = $('#recap_client').attr('value');
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
			search: code, 
			json:true, 
			get:'liste-produits',
			socid: socid
		}	
	})
	.then(function (response){
		if(response.length > 1){ // Plusieurs produits trouvé
			switch_onglet('onglet3');
			$("#onglet3").addClass("ui-btn-active");
			$("#onglet1").removeClass("ui-btn-active");
			$('#corps-3 form input').val(code);
			$ul = $("#liste_produits");
			html = '';
			$.each( response, function ( i, val ) {
                html += "<li><a onclick='ajout_produit("+val.id+");'>" + val.ref +", "+ val.label +", "+ val.price_ttc+"€</a></li>";
            });
            $ul.html( html );
            $ul.listview( "refresh" );
            $ul.trigger( "updatelayout");
		}
		else if(response.length == 1){ // Un seul produit trouvé
			ajout_produit(response[0].id);
		}
		else{ // Pas de produit trouvé
			switch_onglet('onglet3');
			$("#onglet3").addClass("ui-btn-active");
			$("#onglet1").removeClass("ui-btn-active");
			$("#corps-3 form input").val(code);
		}
	});
}

// Supprime une ligne de produit
function delete_ligne_produit(id_ligne) {
	
	if($('#corps-1:hidden').length && $('#corps-3:hidden').length) {
		return false;
	}
	
	
	var id_facture =$("#id_facture").val();
	
	//$("#confirm-ligne-suppresion" ).popup('open'); 
	
	if(!window.confirm('Supprimer la ligne de ticket ?')) {
		return false;
	}
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
			   put:'delete-facture-ligne'
			   ,json:1
			   ,id_ligne:id_ligne
			   ,facture : $("#id_facture").val()
		}
	})
	.then(function (data){
		vider_all();
		refreshTicket(data);
	});
	
	
}

// Charge un ticket
function load_ticket(id_facture, id_div) {
	$('#liste_tickets .ui-link-inherit').removeClass('select');
	$('#'+id_facture).addClass('select');
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
			   get:'facture-lines'
			   ,json:1
			   ,facture : id_facture
		}
	})
	.then(function (data){
		refreshTicket(data, id_div);
	});
}

// Ajout d'une ligne de facture
function ajout_produit(id_produit, noredirect){
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		async : false,
		data: {
			   put:'add-facture-ligne',
			   json:1,
			   produit: id_produit,
			   facture : $("#id_facture").val(),
			   quantite: $("#recap_quantite").attr("value"),
			   remise_percent: $('#recap_remise').attr('value'),
			   prix: $("#recap_prix").attr('value'),
			   addline_predefined: 1
		}
	})
	.then(function (data){
		refreshTicket(data);
		
		if(noredirect!=null && noredirect) {
			
		}
		else{
			vider_produit();
			switch_onglet("onglet1");
			$("#onglet3").removeClass("ui-btn-active");
			$("#onglet1").addClass("ui-btn-active");
			scrollToBottom();
	
			
		}
		
	});
}

function refreshTicket(data, id_div) {
	
	if(id_div==null)id_div='ticket-lignes';
	
	var nb_product=0;
	$("#"+id_div).empty();
	$.each(data, function (i,ligne){
		
		if($('#'+id_div).length==0) {
			alert('Erreur, la zone ticket est introuvable');
		}
		else {
			
			bloc_prix = ligne.total_ligne_ttc+' &euro;<br />x '+ligne.qte;
			
			/*if(ligne.tx_remise>0) {
				bloc_prix ='<span style="text-decoration:line-through;font-size:14px;">'+ligne.total_avant_remise+' &euro;<br /></span>' +bloc_prix
			}*/
			
			html_ligne = '<li>'
					+'<a href="javascript:;" onclick="delete_ligne_produit('+ligne.rowid+')" style="font-size:3px; height:50px;">'
						+'<p class="ui-li-aside ui-li-desc" style="width:100px;">'
							+'<strong style="font-size:17px;top:-5px;position:relative;">'+bloc_prix+'</strong></p>'
							+'<h3 class="ui-li-heading" style="margin:0 0 15px 0;">'+ligne.label+'</h3>';
													
			//if(ligne.ref!=null)	html_ligne+='<p class="ui-li-desc">Réf. : '+ligne.ref+'</p>';

			if(ligne.tx_remise>0)html_ligne+='<p class="ui-li-desc">Remise : '+ligne.remise+'%';

			html_ligne +='</p>'	+'</a>'	+'</li>';
			
			nb_product+=ligne.qte;
			
			$('#'+id_div).append(html_ligne);
			
		}
		
		if(id_div=='ticket-lignes') {
			$("#recap_total_ht").attr('value',ligne.total_ht).empty().append(ligne.total_ht);
			$("#recap_total_tva").attr('value',ligne.total_tva).empty().append(ligne.total_tva);
			$("#recap_total_ttc").attr('value',ligne.total_ttc).empty().append(ligne.total_ttc);
			$("#total_non_payer").attr('value',ligne.reste_payer).empty().append(ligne.reste_payer);
			$("#total_payer").attr('value',ligne.paye_partiel).empty().append(ligne.paye_partiel);
			$("#recap_client").attr('value',ligne.socid).empty().append(ligne.socname);
			
			//$("#recap_vendeur").attr('value',ligne.id_vendeur).empty().append(ligne.nom_vendeur);
			
			$("#recap_vendeur").val(ligne.id_vendeur);
			$("#recap_vendeur_name").html(ligne.nom_vendeur);
			
			
			$("#recap_nb_product").html(nb_product);
		}
		
		
	});
	
	$('#'+id_div).listview("refresh");
	scrollToBottom();
}

// Ajout d'une ligne de remise
function ajout_remise(remise_mt, label){
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
				put:'add-facture-remise',
				json:1,
			    facture : $("#id_facture").val(),
			    remise_mt: remise_mt,
			    label: label
		}
	})
	.then(function (data){
		refreshTicket(data);
		vider_produit();
		vider();
	});
}

// Création d'une nouvelle facture
function creer_facture(){
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
			put : 'creer-facture',
			json:1
		}
	})
	.then(function(val){
		$("#id_facture").val(val.id_facture);
		$("#recap_client").attr('value',val.id_client).empty().append(val.nom);
	});
}

// Annulation de la facture en cours
function annuler_facture(id_facture){
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		data: {
				put : 'annuler-facture'
				,facture: id_facture
			}	
	})
}

// Cloturation de la facture
function validation_vente(id_reglement){
	
	montant_paiement = parseFloat($("#texte").val());
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		data: {
			put : 'valider-vente',
			facture : $("#id_facture").val(),
			montant : montant_paiement,
			reglement : id_reglement,
			client : $('#recap_client').text(),
			id_vendeur : $('#recap_vendeur').val()
		}
	})
	.then(function (montant){
		_refreshMontant(montant,montant_paiement);
	});
}


function _refreshMontant(montant, montant_paiement, retour_impression){
	montant = parseFloat(montant);
	
	/*[PH] j'ai rajouté le test sur retour_impression pour rentrer dans le if si on viens de cliquer sur le bouton "Utiliser Bon d'achat" avec un bon d'achat qui couvre la totalité du paiement */
	if(montant > 0){ //facture partiellement réglé
		paiement_partiel = parseFloat($('#total_payer').attr('value').replace(",","."));
		$('#total_payer').attr('value',String((paiement_partiel + montant_paiement).toFixed(2)).replace(".",",")).empty().append($('#total_payer').attr('value'));
		$('#total_non_payer').attr('value',String(montant.toFixed(2)).replace(".",",")).empty().append($('#total_non_payer').attr('value'));
		vider();
	}else if(montant <= 0){ //facture totalement réglé AVEC monnaie rendu
		if(CAISSE_DO_NOT_PRINT_TICKET_EACH_TIME == 0) {
			imprimer_ticket($('#id_facture').val(),$('#recap_client').attr('value'),$('#recap_vendeur').val(),montant);
		}
		vider_all();
		if(montant<0)alert('A rendre : '+Math.abs(montant)+' €');
		creer_facture();
	}
}

// Changement du type d'affichage des tickets : en attente ou archivé
function changement_liste_ticket(type)
{
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
				get : 'liste-tickets',
				json : 1,
				search : '',
				type : type}
	})
	.then(function(response){
		_display_liste('ticket',response);
		$('#liste_tickets .ui-link-inherit').removeClass('select');
	});
}

// Création ou modification d'un client
function update_client(id,id_contact,nom,prenom,civilite,adresse,cp,email,datenaiss,ville,tel,encours_init,id_button,barcode){
	
	$.ajax({
		url: "ajax/interface.php",
		dataType : "json",
		crossDomain: true,
		data: {
			put: 'traitement-client',
			json:1,
			id : id,
			id_contact : id_contact,
			nom : nom,
			prenom : prenom,
			civilite : civilite,
			adresse : adresse,
			cp : cp,
			email : email,
			datenaiss : datenaiss,
			ville: ville,
			tel : tel,
			barcode : barcode,
			encours_init: encours_init,
			id_button : id_button
		}
	})
	.then(function(response){
		$('#block_valider, #block_annuler, #block_validerandassoc, #block_fidelite').hide();
		$('#block_nouveau').show();
		$('#form-client input').val('');
		$('#form-client').hide();
		$('#contener_client').show();
		if(response.id_button == "validerandassoc")
			affecter_client(response.socid,response.prenom,response.nom);
		
		init_liste($('#liste_clients'),$('#contener_client input').val(),"client");	
	});
}

// Remplissage du formulaire de modification
function infos_client(id_societe){
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {
				get:'infos-client',
				json:1,
				societe : id_societe
		}
	})
	.then(function(client){
		$('#radio-choice-'+client.civilite).click().checkboxradio( "refresh" );
		$("#id_client").val(client.id);
		$("#id_contact").val(client.id_contact);
		$('#textinput-nom').val(client.nom);
		$('#textinput-prenom').val(client.prenom);
		$('#textinput-adresse').val(client.adresse);
		$('#textinput-cp').val(client.cp);
		$('#textinput-ville').val(client.ville);
		$('#textinput-email').val(client.email);
		$('#textinput-tel').val(client.tel);
		$('#textinput-datenaiss').val(client.datenaiss);
		$('#textinput-encours').val(client.encours);
		$('#textinput-encours-init').val(client.encours_init);
		$('#textinput-barcode').val(client.barcode);
		$('#fiche-client-civilite').val(client.civilite);
		$('#radio-choice-'+client.civilite).click().checkboxradio( "refresh" );
		$('#block_valider, #block_annuler, #block_validerandassoc, #block_fidelite').show();
		$('#block_nouveau').hide();
		$('#contener_client, #erreur').hide();
		$('#form-client').show();
		if(client.civilite == "") {
			$('#radio-choice-MME').click().checkboxradio( "refresh" ).click();
		}
			
		
			
	});
}

// Impression du ticket
function imprimer_ticket(id_facture,id_client,id_vendeur,montant,sansprix){
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		async: false
		,dataType:'json'
		,data: {
			   get:'impression'
			   ,facture : id_facture
			   ,client : id_client
			   ,vendeur : id_vendeur
			   ,montant : montant
			   ,sansprix : sansprix
			   ,json:1
		}
	}).done(function(result) {
		
		if(result!='') {
			/*
			 * Ticket à afficher
			 */
			if($('#pop-ticket').length==0) {
				$('body').append('<div class="printable" id="pop-ticket" style="position:absolute; bottom:0; right:0; background-color:#fff; text-align:left; margin:auto; margin-top:5px;"></div>');	
			}
			$('#pop-ticket').html(result);
			self.print();
			//$('#pop-ticket').dialog();
		}
		
		if( CAISSE_SEND_MAIL_EVERYTIME ) {
			
			sendBillMail(id_facture);
			
		}
		
		
	});
}

function add_remise_globale(remise, label){
	if($('#total_non_payer').attr('value') != 0)
	{
		$.ajax({
			url: "ajax/interface.php",
			crossDomain: true,
			data: {
				   put:'remise'
				   ,facture : $('#id_facture').val()
				   ,remise : remise
				   ,label: label
			}
		})
		.then(function(response){
			load_ticket($('#id_facture').val())
			vider();
		});
	}
	else
		vider();
}

// Affectation d'un client à la vente en cours
function affecter_client(socid, prenom, nom){
	$("#recap_client").attr('value',socid).empty().append(prenom+' '+nom);
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		async: false,
		data: {
			   put:'affecter-client'
			   ,facture : $("#id_facture").val()
			   ,client : $("#recap_client").attr('value')
			   ,json:1
		},
		dataType:'json'
	}).done(function(client) {
		
		if(client.nbPoint>0) {
			$('#button-use-reward-point').show();
			
			$('#button-use-reward-point .counter').html(client.nbPoint);
		}
		else{
			$('#button-use-reward-point').hide();
		}
		
	});
	
	switch_onglet("onglet1");

}

// Récupération des infos de la facture pour la création d'un bon d'achat "avoir"
function getFacture(id_ticket_attente){
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		dataType: "json",
		data: {
			   get:'get-facture'
			   ,json:1
			   ,facture : id_ticket_attente
		}
	})
	.then(function(response){
		$('#id_client_bonachat').val(response.id_client);
		$('#textinput-client').val(response.client);
		$('#textinput-ticket').val(response.refticket);
		$('#textinput-montant').val(response.montant).focus();
	});
}

// Création d'un bon d'achat
function createBonachat(id_reglement){
	if($('#id_facture_bonachat').val() == ''){ //cadeau
		type = 'ACOMPTE';
		facture = 0;
	}
	else{ //avoir
		type = 'AVOIR';
		facture = $('#id_facture_bonachat').val();
	}
	socid = $('#id_client_bonachat').val();
	montant = $('#textinput-montant').val();
	
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		dataType: "json",
		data: {
			   put:'create-bonachat'
			   ,json:1
			   ,id_facture : facture
			   ,type : type
			   ,socid : socid
			   ,montant : montant
			   ,id_reglement : id_reglement
		}
	})
	.then(function(){
		vider_all();
		creer_facture();
		switch_onglet("onglet1");
	});
}


// Utilisation d'un bon d'achat
function useBonachat(){
	if(id_ba_attente != '' && $('#recap_total_ttc').attr('value') != "0"){
		montant = $('#recap_total_ttc').attr('value').replace(",",".");
		$.ajax({
			url: "ajax/interface.php",
			crossDomain: true,
			dataType: "json",
			data: {
				   put:'use-bonachat'
				   ,json:1
				   ,id : id_ba_attente
				   ,montant : montant
				   ,facture : $('#id_facture').val()
				   ,nom_client : $('#recap_client').val()
			}
		})
		.then(function(response){
			_refreshMontant(response.montant, response.montant_paiement, response.retour_impression);
			refreshBonAchat();
			switch_onglet('onglet1');
		});
	}
	id_ba_attente = '';
}

function printBonachat(){
	if(id_ba_attente != ''){
		$.ajax({
			url: "ajax/interface.php",
			crossDomain: true,
			async: false,
			dataType: "json",
			data: {
				   put:'imprime-bonachat'
				   ,json:1
				   ,id : id_ba_attente
			}
		});
	}
	id_ba_attente = '';
}

function rechercher_ville(request, response){
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {search: request.term, json:true, get:'get-ville-client'}	
	})
	.then(function (data){
		response($.map(data, function (item)
		{
			return {
				label: item.cp + ", " + item.ville,
				value: function ()
				{
					if ($(this).attr('id') == 'textinput-cp')
					{
						$('#textinput-ville').val(item.ville);
						return item.cp;
					}
					else
					{
						$('#textinput-cp').val(item.cp);
						return item.ville;
					}
				}
			};
		}));
	});
}

function setViewForGaucher(gaucher) {
	
	if(gaucher==1) {
	
		$('#main_left').css('float' , 'right');
		$('#main_right').css('float' , 'none');
		
		$('#checkbox-gaucher').prop('checked', true).checkboxradio('refresh');
		
		$('#back-to-onglet1').css({
			right : 'auto'
			, left:'20px'
		});
		
	}
	else{
		
		$('#main_left').css('float' , 'left');
		$('#main_right').css('float' , 'left');
		
		$('#checkbox-gaucher').prop('checked', false).checkboxradio('refresh');
		
		$('#back-to-onglet1').css({
			left: 'auto'
			, right:'20px'
		});
		
	}
		
	
}

function changeVendeur(id_vendeur,id_facture){
	$.ajax({
		url: "ajax/interface.php",
		crossDomain: true,
		async: false,
		dataType: "json",
		data: {
			   put:'change-vendeur'
			   ,json:1
			   ,id_vendeur : id_vendeur
			   ,id_facture : id_facture
		}
	}).then(function (vendeur){
		
		setViewForGaucher(vendeur.array_options.options_caisse_gaucher);
		
	});
}

function getCategorie(id_categorie) {
	
	$.ajax({
		url: "ajax/interface.php",
		dataType: "json",
		crossDomain: true,
		data: {id_categorie: id_categorie, json:true, get:'categories'}	
	})
	.then(function (data){
		if(id_categorie == 0 || CAISSE_USE_SIMPLE_CATEGORY_MODE) {
			$('#categories-list').empty();
			$('#liste_produits_cat').hide();
			$('#categories-list-fille').hide();
			
			$('#categories-list').css({ width : '100%' });
		}
		else {
			$('#liste_produits_cat').show();
			$('#categories-list-fille').show();
			
			$('#categories-list').css({ width : '10%' });
		}
		
		$('#categories-list-fille').empty();
		
		var color_cat = '';
		
		$.each(data, function(i, item) {
			
			if(item.view_photo=='')item.view_photo='./img/nophoto.png';
			if(item.array_options && item.array_options.options_couleur!='') color= '#'+item.array_options.options_couleur;
			else color = '#000';
			
			if(item.current) {
				color_cat = color;
				datatheme = 'e'; 
				
				$('#entete-produit').html('<table width="100%"><tr><td width="140" class="racine">&nbsp;</td><td><div style="position:relative; float:none; width:100%; height:120px; background:url('+item.view_photo_large+'); background-size:cover; background-repeat: no-repeat; background-position: center center;"><div align="center" style=" white-space: normal; position:absolute; bottom:0;left:0; background-color:'+color+'; font-size:24px; padding:10px; border-top-right-radius:10px;">'+item.label+'</div></div></td></tr></table>');
			}
			else if(id_categorie == 0 || CAISSE_USE_SIMPLE_CATEGORY_MODE){
				datatheme = 'b';
				$('#categories-list').append('<a href="javascript:getCategorie('+item.id+')" data-mini="true" data-inline="true" data-corners="false" data-role="button" data-theme="'+datatheme+'"><div style="position:relative;"><div style="width:100px; overflow:hidden; text-align:center;"><img src="'+item.view_photo+'" style="height:100px" /></div><div align="center" style="width:100px; white-space: normal; position:absolute; bottom:0;left:0; background-color:'+color+';">'+item.label+'</div></div></a>');
			}
			else{
				datatheme = 'e';
				$('#categories-list-fille').append('<a href="javascript:getCategorie('+item.id+')" data-mini="true" data-inline="true" data-corners="false" data-role="button" data-theme="'+datatheme+'"><div style="position:relative;"><div style="width:100px; overflow:hidden; text-align:center;"><img src="'+item.view_photo+'" style="height:100px" /></div><div align="center" style="width:100px; white-space: normal; position:absolute; bottom:0;left:0; background-color:'+color+'; color:#fff; text-shadow: 0 1px 0 #000; overflow:hidden;">'+item.label+'</div></div></a>');
			}
			
			
		});
		
		if($('#categories-list-fille').html() == '') {
			
			/* TODO */
			
		}
		
		
		if(id_categorie>0) {
			var item = {
				id : 0
				,view_photo : "./img/home.png"
				,label : "Racine"
			};
			
			$('#entete-produit .racine').html('<a href="javascript:getCategorie('+item.id+')" data-mini="true" data-inline="true" data-corners="false" data-role="button" data-theme="b"><div style="position:relative;"><div style="width:100px; overflow:hidden; text-align:center;"><img src="'+item.view_photo+'" style="height:100px" /></div><div align="center" style="width:100px; white-space: normal; position:absolute; bottom:0;left:0; background-color:#000;">'+item.label+'</div></div></a>');
			$('#produits').hide();
		} 
		else{
			$('#produits').show();
			$('#entete-produit').empty();
		}
		
		$('#categories-list a[data-role=button],#categories-list-fille a[data-role=button], #entete-produit a[data-role=button]').button();	
		
		//$('#categories-list').button( "refresh");
		getProductForCategorie(id_categorie, color_cat);
		
		
	});
	
}

function getProductForCategorie(id_categorie, color) {
	var nbProduit = 0;
	
	if(id_categorie==0) {
		$('#liste_produits_cat').empty();
	}
	else {
		$.ajax({
			url: "ajax/interface.php",
			dataType: "json",
			crossDomain: true,
			data: {id_categorie: id_categorie, json:true, get:'productForCat'}	
		})
		.then(function (data){
			$('#liste_produits_cat').empty();
			$.each(data, function(i, item) {
				
				if(item.view_photo=='')item.view_photo='./img/nophoto.png';
				
				$('#liste_produits_cat').append('<a href="javascript:ajout_produit('+item.id+', true);" data-inline="true" data-mini="true" data-corners="false" data-role="button" style="width:120px;"><div style="position:relative;"><div style="width:100px; overflow:hidden; text-align:center;"><img src="'+item.view_photo+'" style="height:100px" /></div><div align="center" style="width:100px; white-space: normal; position:absolute; bottom:0;left:0; background-color:#000;">'+item.label+', '+item.price_ttc_device+'</div></div></a>');
			
				nbProduit++;
				//$('#liste_produits_cat').append('<li><a href="javascript:ajout_produit('+item.id+', true);">'+item.label+'</a></li>');
			});
			$('#liste_produits_cat a[data-role=button]').button();	
			
			$('#liste_produits_cat').css({borderRadius: 10, backgroundColor: color});
			
			
			if(nbProduit == 0) {
				$('#liste_produits_cat').hide();
				if(!CAISSE_USE_SIMPLE_CATEGORY_MODE) $('#categories-list-fille').css({ width : '90%' });
			}
			else{
				$('#liste_produits_cat').show();
				if(!CAISSE_USE_SIMPLE_CATEGORY_MODE) $('#categories-list-fille').css({ width : '10%' });

			}
			
			
		});
		
	}
	
}

function sendBillMail(id_facture){
	
	if(id_facture==null)id_facture=id_ticket_attente;
	
	
	if(id_facture > 0){
		
		$('#popupSendBillMailForm').load(HTTP+"compta/facture.php?facid="+id_facture+"&action=presend&mode=init form[name=mailform]", function() {
			
			$('#popupSendBillMailForm input#cancel, #popupSendBillMailForm input.removedfile, #popupSendBillMailForm input#addedfile, #popupSendBillMailForm input#addfile').remove();
			$('#popupSendBillMailForm form a').contents().unwrap();
			
			
			$('#popupSendBillMailForm form').submit(function() {
									
				$.post( $(this).attr('action'), $( this ).serialize());
				
				$('#popupSendBillMail').popup( "close" ) ;	
				
				return false;
			
			});
			
			$('#popupSendBillMail').popup( "open" ) ;	
			
		});

	}
}

function useRewards() {

	var id_facture = $('#id_facture').val();
//	var nbPoint = $('#button-use-reward-point .counter').html();

	if(id_facture > 0){
			
		$('#popupUseRewardsForm').load(HTTP_REWARDS+"?facid="+id_facture+"&action=usepoints form[name=formpoints]", function() { 
			
			$('#popupUseRewardsForm form').submit(function() {
				//$.post( $(this).attr('action'), $( this ).serialize());
				
				var nbPoint = $(this).find('input[name=points]').val();
				
				ajout_remise( nbPoint * REWARDS_DISCOUNT, "Remise fidélité ("+nbPoint+" points)" );
				
				$.ajax({ /* decrease point */
					url: "ajax/interface.php",
					dataType: "json",
					crossDomain: true,
					data: {
							put:'use-reward',
							json:1,
						    facture : $("#id_facture").val(),
						    nb_point: nbPoint,
						   
					}
				});
				
				$('#popupUseRewards').popup( "close" ) ;
				$('#popupUseRewardsForm').html('');
				
				set_focus();
				
				return false;
			});
			
			
			$('#popupUseRewards').popup("open");
			
		});

	}
}
function close_fidelite_dialog() {
	$('#popupManageRewards').popup("close");
}
function open_fidelite_dialog() {
	
	var id_client = $("#id_client").val();
	
	//scale iframe
	var scrWidth = $( window ).width() - 100,
        scrHeight = $( window ).height() - 100,
        ifrPadding = 2,
        ifrBorder = 2,
        ifrWidth = scrWidth ,
        ifrHeight = scrHeight ,
        h, w;

    if ( ifrWidth < scrWidth && ifrHeight < scrHeight ) {
        w = ifrWidth;
        h = ifrHeight;
    } else if ( ( ifrWidth / scrWidth ) > ( ifrHeight / scrHeight ) ) {
        w = scrWidth;
        h = ( scrWidth / ifrWidth ) * ifrHeight;
    } else {
        h = scrHeight;
        w = ( scrHeight / ifrHeight ) * ifrWidth;
    }
	
	$('iframe[name=popupManageRewardsIframe]').css({
		width: w+'px'
		,height: h+'px'
	});
	
	url = HTTP_MANAGE_REWARDS+'?socid='+id_client+'&hidemenu=1';
	
	$('iframe[name=popupManageRewardsIframe]').attr('src', url);
	
	$('#popupManageRewards').popup("open");
	
	
}
