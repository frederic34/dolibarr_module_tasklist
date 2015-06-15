<!-- <div data-role="popup" id="confirm-add-time" data-overlay-theme="a" data-theme="c" style="max-width:400px;">
	<div data-role="header" data-theme="a" class="ui-corner-top">
		<h1>Temps à ajouter</h1>
	</div>
	<div data-role="content" data-theme="d" class="ui-corner-bottom ui-content">
		<p class="temps_passe"></p>
		<a href="javascript:$('#confirm-add-time').popup('close');" data-role="button" data-inline="true" data-rel="back" data-theme="c">Annuler</a> <a href="#" data-role="button" data-inline="true" data-rel="back" data-transition="flow" data-theme="b">Valider</a>  
	</div>
</div> -->


<div data-role="popup" id="confirm-add-time" data-theme="a">
    <div data-role="popup" id="popupAddTime" data-theme="a" class="ui-corner-all">
        <form>
            <div style="padding:10px 20px;">
              <h3>Temps passé</h3>
              <input id="heure" value="" placeholder="H" data-theme="c" type="text" style="width: 35px;"> h 
              <input id="minute" value="" placeholder="m" data-theme="c" type="text" style="width: 35px;"> m<br><br>
              <button type="button" data-theme="b" data-icon="check" onclick="$('#confirm-add-time').popup('close');">Annuler</button> <button type="submit" data-theme="b" data-icon="check">Valider</button>
            </div>
        </form>
    </div>
</div>