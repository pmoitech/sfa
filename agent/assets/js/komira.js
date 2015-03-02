//foto del streeview
//http://maps.googleapis.com/maps/api/streetview?size=400x400&location=3.42056,-76.5222&sensor=true

var http = location.protocol;
var slashes = http.concat("//");

var server = slashes.concat(window.location.hostname) + '/es/';

//console.log(server);
var lat = lng = deslat = destlng = 0;
var scode = null;
var user = null;
var localizationDemonId;
var busLocationDemonId;
var updateLocationDemonId;
var verification_interval = null;
var updatelocation_interval = null;
var verifyServiceDemonId;
var verifyServiceStateDemonId;
var WaitVeryServiceDemonid;
//var geoOptions = { timeout: verification_interval };
//var ubicacionServicio = null;
var request_id = null;
var username = null;
var password = null;
var switchBgDemon = null;
var lat_user = null;
var lng_user = null;
var placa = null;
var fecha_sos = null;
var page_state  = 'do-login';


var styles = [
                  {
                        "featureType": "poi",
                        "stylers": [
                          { "visibility": "off" }
                        ]
                      },{
                        "featureType": "transit",
                        "stylers": [
                          { "visibility": "off" }
                        ]
                      },{
                        "featureType": "landscape.man_made",
                        "stylers": [
                          { "visibility": "off" }
                        ]
                      }
                    ];
    
var map;
var markersArray = [];
var agentMarker;
var novedadesArray = [];
var idsucursalruta;

var NotUpdateArray = [];
var flagErrorUpdate = 'true';


window.onpopstate = function(event) {
 if (window.history && window.history.pushState) {
    $(window).on('popstate', function() {
      var hashLocation = location.hash;
      var hashSplit = hashLocation.split("#!/");
      var hashName = hashSplit[1];
      
      if (hashName !== '') {
        var hash = window.location.hash;
        if ((NotUpdateArray.length > 0) && (hash === '') && (page_state==='dashboard')) {
          alert('Existen cambios en los estados de los alumnos que no han sido actualizados en el servidor, por favor espere hasta que se actualicen automáticamente cuando se restablezca la señal de internet.');
          history.go(1); 
        }
      }
    });
  }
};


$(document).ready(function() {
      // $.mobile.loading( "show" );
    init();
    
    $('#do-login').click(function(e){
		e.preventDefault();
        getArrayNovedades();
        username = $('#username').val();
        password = $('#password').val();
        login(username, password);
    });
    
    $('#btn-refresh').click(function(e){
        e.preventDefault();
        getWayStop();
    });

    $('#btn-maps-modal').click(function(e){
        e.preventDefault();
        clearInterval(busLocationDemonId);
        busLocationDemonId = setInterval(getBusLocation, 5000);
        getBusLocation();
        getStudentStop();
        $.mobile.changePage('#maps-modal', { transition: "pop", role: "dialog", reverse: false } );
    });

   $('#btn-stop-back').click(function(e){
        e.preventDefault();
        getStudentStop();
        $("#det-parada-modal").dialog('close');
    });

    $('#btn-map-back').click(function (e){
        clearInterval(busLocationDemonId);
        deleteOverlays();   
        deleteOverlaysBus();   
    });

    $('#btn-close').click(function(e){
        e.preventDefault();
        if ((NotUpdateArray.length > 0) && (page_state==='dashboard')) {
            alert('Existen cambios en los estados de los alumnos que no han sido actualizados en el servidor, por favor espere hasta que se actualicen automáticamente cuando se restablezca la señal de internet.');
        }else{
            clearInterval(busLocationDemonId);
            clearInterval(localizationDemonId);
            clearInterval(verifyServiceDemonId);
            clearInterval(updateLocationDemonId);    
            password = '';
            page_state  = 'do-login';
            $("#show-login").trigger('click');
        }
    });
    
});

function validarEnter(e) {
    if (window.event) {
        keyval=e.keyCode
    } else 
        if (e.which) {
            keyval=e.which
        } 
    if (keyval=="13") {
        e.preventDefault();
        getArrayNovedades();
        username = $('#username').val();
        password = $('#password').val();
        login(username, password);
    } 
}

$( document ).bind( "pageshow", function( event, data ){
google.maps.event.trigger(map_canvas, 'resize');
});

$(document).on('pagebeforeshow', '#maps-modal', function(){ 
    $('#map_canvas').css('width', '100%');
    $('#map_canvas').css('height', '500px');
    //console.log('cargando mapa');
    cargarMapa();
    
 });


function closeApp(){
   
    $.ajax({
        type : "GET",
        url : server + 'agent/close',        
        dataType : "json",
        data : {}
    }).done(function(response){
        //    
    }); 

}    


function login(id, key){
    clearInterval(localizationDemonId);
	clearInterval(verifyServiceDemonId);
    
    $.ajax({
        type : "GET",
        url : server + 'api/login',        
        dataType : "json",
        data : {
            username : id,
            password : key,
            hms1: scode
        }
    }).done(function(response){
        
        if(response.state=='ok'){
            page_state  = 'dashboard';
            $("#show-dashboard").trigger('click');
            user = response.data
            $('#agent-name').html(user.nombre+' : '+user.nombreruta);
            
            getWayStop();
            
            $('#agent-photo').attr('src', "../assets/images/agents/" + user.foto) ;
            // ojoooo no se puede sacar clearInterval de este lado por que no se reinicia el logueo
            clearInterval(updateLocationDemonId);    
            //localizame() debe ir siempre primero que UpdateLocation, por si no hay conexion a la red.
            localizame();
            localizationDemonId = setInterval(localizame, verification_interval);
			
            updateLocation();
			updateLocationDemonId = setInterval(updateLocation, verification_interval);
            
            idsucursalruta = user.sucursalruta;
            
        }else{
            alert(response.msg);
        }
    });     
}


function updateLocation(){
	
	$('#current-position').parent().css('background-color', 'yellow');
    $('#current-position').css('color', 'black');
    $('#current-position').val('Latitud: ' + lat + ' Longitud: ' + lng);
	
    $.ajax({
        type : "GET",
        url : server + 'agent/update_location',        
        dataType : "json",
        timeout : 5000,
        data : {
            lat : lat,
            lng : lng,
            cachehora : (new Date()).getTime()
        },
        
    }).done(function(response){
    		$('#current-position').parent().css('background-color', '#FFFFFF');
    		
            if(response.state != 'ok'){
                $('#current-position').val('-------------------------');
            }else{
            	$('#current-position').val('Latitud: ' + lat + ' Longitud: ' + lng);
            }

            //verificar en el arreglo de errores de actualizacion 
            if (flagErrorUpdate=='true')
                verifyErrorUpdate();

     }).fail(function(jqXHR, textStatus, errorThrown){
    	 //$('#current-position').val('======= Error de conexión =======');
         $('#current-position').parent().css('background-color', '#FFFFFF');
         $('#current-position').css('color', 'red');
         $('#current-position').val('Latitud: ' + lat + ' Longitud: ' + lng);
         login(username, password);
      }); 
     //verificar mensaje de ayuda de otros agentes.
     //get_sos();
}

//maximumAge: Se trata de un valor en milisegundos que establece un intervalo dentro del cual aún se acepta la última posición cacheada que pueda tener almacenado el cliente. Si no se especifica, su valor por defecto es 0, que significa que se iniciará una nueva petición sin tener en cuenta las posiciones cacheadas.
function localizame() {
    if (navigator.geolocation) { 
		//navigator.geolocation.getCurrentPosition(coords, errores);
        navigator.geolocation.getCurrentPosition(coords, errores,{'enableHighAccuracy':true,'timeout':20000,'maximumAge':0});
    }else{
        $('#current-position').val('No hay soporte para la geolocalización.');
    }
}

function coords(position) {
    lat = position.coords.latitude;
    lng = position.coords.longitude;
}

function cargarMapa() {
    var directionsDisplay = new google.maps.DirectionsRenderer();
    var directionsService = new google.maps.DirectionsService();
    var latlon = new google.maps.LatLng(lat,lng); /* Creamos un punto con nuestras coordenadas */
    var myOptions = {
        zoom: 15,
        center: latlon, /* Definimos la posicion del mapa con el punto */
        navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL}, 
        mapTypeControl: true, 
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles : styles

    };/* HYBRID  Configuramos una serie de opciones como el zoom del mapa y el tipo.*/
    map = new google.maps.Map($("#map_canvas").get(0), myOptions); /*Creamos el mapa y lo situamos en su capa */
    //pinta el colegio de la sucursal.
    getOfficeLocation(idsucursalruta);  
}


function getBusLocation(){
    deleteOverlaysBus() ;
    agentMarker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lng),
            map: map,
            draggable: true,
            icon : 'assets/images/bus.png'
        });
}

//---------------------------------
// Iconos de paradas de estudiantes.

function clearOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
  }
}

// Shows any overlays currently in the array
function showOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(map);
    }
  }
}

function deleteOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
    markersArray.length = 0;
  }
}

function deleteOverlaysBus() {
    if(agentMarker){
        agentMarker.setMap(null);
        agentMarker = null;
    }
}

function getOfficeLocation(idsucursal){
    $.ajax({
        type : "GET",
        url : server + 'api/get_office_location',        
        dataType : "json",
        data : {
            cachehora : (new Date()).getTime(),
            idsucursal  : idsucursal
        }
        
    }).done(function(response){
        var coordenadas;
        if(response.state == 'ok'){
            coordenadas =  new google.maps.LatLng( response.result.latitud, response.result.longitud);
            setOfficeIcons(coordenadas);
        }
    });
  
}

var iconOffice;
function setOfficeIcons(coordenadas){
    if(iconOffice)
        iconOffice.setMap(null);
    iconOffice = new google.maps.Marker({
            position:coordenadas,
            map: map,
            icon : 'assets/images/colegio.png'
    });
    iconOffice.setMap(map);
}


function getStudentStop(){
    var idturno = $('#select-turno').val();
    $.ajax({
        type : "GET",
        url : server + 'agent/get_stop_location_way',        
        dataType : "json",
        timeout : 5000,
        data : {
            idturno : idturno,
            cachehora : (new Date()).getTime(),
        },
    }).done(function(response){

        if(response.state == 'ok'){
            var coordenadas;
            deleteOverlays();
           for(var i in response.result){
                coordenadas =  new google.maps.LatLng( response.result[i].latitud, response.result[i].longitud);
                setIcons(coordenadas, response.result[i]);
            }
            
        }
    });
  
}

function setIcons(coordenadas, result){
    var popup;
    var icon_casa
    if(result.estado==1)
        icon_casa =  'assets/images/casa2.png';
    else
        icon_casa =  'assets/images/casa.png';
          
    iconMarker = new google.maps.Marker({
            position:coordenadas,
            map: map,
            animation: google.maps.Animation.DROP, 
            draggable: false,
            icon : icon_casa,
            title : result.nombre+' - '+result.direccion+' - '+result.telefono
    });
    markersArray.push(iconMarker);

    google.maps.event.addListener(iconMarker, 'click', function(evento){
            $('#idparada').val(result.idparada);
            $('#idalumno').val(result.idalumno);
            //$('#latitud').val(result.latitud);
            //$('#longitud').val(result.longitud);
            $('#latitud').val(evento.latLng.lat());
            $('#longitud').val(evento.latLng.lng());

            $('#nombre').val(result.nombre);
            
            $('#telefono').val(result.telefono);
            
            $('#direccion').val(result.direccion);
            $('#descripcion').val(result.descripcion);
            if(result.foto1!=null)
                $('#foto1').attr('src', "../assets/images/students/" + result.foto1) ;
            else
                $('#foto1').attr('src', "" ) ;
            if(result.foto2!=null)    
                $('#foto2').attr('src', "../assets/images/students/" + result.foto2) ;
            else
                $('#foto2').attr('src', "" + result.foto1) ;
            
            $.mobile.changePage('#det-parada-modal', { transition: "pop", role: "dialog", reverse: false } );
    });    
}
//---------------------------------


function errores(err) {
    /*Controlamos los posibles errores */
    if (err.code == 0) {
        $('#current-position').val("Error en la geolocalización.");
    }
    if (err.code == 1) {
        $('#current-position').val("Para utilizar esta aplicación por favor aceptar compartir tu posición gegrafica.");
    }
    if (err.code == 2) {
        $('#current-position').val("No se puede obtener la posición actual desde tu dispositivo.");
    }
    if (err.code == 3) {
        $('#current-position').val("Hemos superado el tiempo de espera. Vuelve a intentarlo.");
    }
}


var sector = null;
var formatted_addr = null;
var geocoder = new google.maps.Geocoder();


function init(){
    $.ajax({
        type : "GET",
        url : server + 'api/agent_init',        
        dataType : "json",
        data : {}
    }).done(function(response){
        $.mobile.loading( "hide" );
        if(response.state == 'ok'){
            scode = response.code;
            verification_interval = response.verification_interval;
            updatelocation_interval = response.updatelocation_interval;
            $('#app_name').html(response.app_name);
            $('#copyright').html(response.copyright);
            $('#copyright2').html(response.copyright);
           
        }else{
            $('#popupBasic').html('No hay conexión al servidor, intente de nuevo mas tarde.');
            $('#popupBasic').popup();
        }
    }); 
    
}


function getWayStop(){
    var idturno = $('#select-turno').val();
    $.ajax({
        type : "GET",
        url : server + 'agent/get_stop_location_way',        
        dataType : "json",
        timeout : 5000,
        data : {
            idturno : idturno,
            cachehora : (new Date()).getTime(),
        },
        
    }).done(function(response){
            
            if(response.state == 'ok'){
                var html='';
                $('#student_stop').empty();
                for(var i in response.result){
                   html = html + PaintWayStop(response.result[i]);
                }
                
                $('#student_stop').html(html);
                $('#student_stop').trigger( "create" );
            }else{
                console.log('Error cargando datos get_stop_location_way');
            }
            
     }).fail(function(jqXHR, textStatus, errorThrown){
         //login(username, password);
      }); 
}

function PaintWayStop(data){
    var html;
    //estado : 1 en casa, 2 rumbo al colegio, 3 en el colegio, 4 rumbo a casa
    if ((data.estado==2)||(data.estado==4))
        html  = '<fieldset name="fieldset-'+data.idalumno+'" id="fieldset-'+data.idalumno+'" data-role="collapsible" data-theme="e" data-content-theme="d">';
    else
        if ((data.estado==5))
            html  = '<fieldset name="fieldset-'+data.idalumno+'" id="fieldset-'+data.idalumno+'" data-role="collapsible" data-theme="c" data-content-theme="d">';
        else
            html  = '<fieldset name="fieldset-'+data.idalumno+'" id="fieldset-'+data.idalumno+'" data-role="collapsible" data-theme="a" data-content-theme="d">';
    html += '   <legend>'+data.nombre+'</legend>';
    if (data.foto1!=null)
        html += '   <img id="photo-boy1" alt="" style="width: 50px; height: 50px" src="../assets/images/students/'+data.foto1+'" >';
    if (data.foto2!=null)
        html += '   <img id="photo-boy2" alt="" style="width: 50px; height: 50px" src="../assets/images/students/'+data.foto2+'" >';
   
    html += '   <label for="textinput-f">Dirección: '+data.direccion+', Teléfono: '+data.telefono+' </label>';
   
    html += '   <div data-role="controlgroup">';
    html += '       <input name="ckbox-2-'+data.idalumno+'" id="ckbox-2-'+data.idalumno+'" type="checkbox" ';
    if (data.estado==2)
        html += '  checked="checked" ' ;
    html +=     ' onchange="updateStateStudent('+data.idalumno+',2)" >';
    html += '       <label for="ckbox-2-'+data.idalumno+'">En camino al colegio</label>';
    
    html += '       <input name="ckbox-3-'+data.idalumno+'" id="ckbox-3-'+data.idalumno+'" type="checkbox" ';
    if (data.estado==3)
        html += '  checked="checked" ' ;
    html +=     ' onchange="updateStateStudent('+data.idalumno+',3)" >';
    html += '       <label for="ckbox-3-'+data.idalumno+'">En el colegio</label>';
    
    html += '       <input name="ckbox-4-'+data.idalumno+'" id="ckbox-4-'+data.idalumno+'" type="checkbox" ';
    if (data.estado==4)
        html += '  checked="checked" ' ;
    html +=     ' onchange="updateStateStudent('+data.idalumno+',4)" >';
    html += '       <label for="ckbox-4-'+data.idalumno+'">De regreso a casa</label>';
    
    html += '       <input name="ckbox-1-'+data.idalumno+'" id="ckbox-1-'+data.idalumno+'" type="checkbox" ';
    if (data.estado==1)
        html += '  checked="checked" ' ;
    html +=     ' onchange="updateStateStudent('+data.idalumno+',1)" >';
    html += '       <label for="ckbox-1-'+data.idalumno+'">Entregado en casa</label>';
    
    html += '       <input name="ckbox-5-'+data.idalumno+'" id="ckbox-5-'+data.idalumno+'" type="checkbox" ';
    if (data.estado==5)
        html += '  checked="checked" ' ;
    html +=     '  >';
    if (data.estado==5)
        html += '       <label for="ckbox-5-'+data.idalumno+'">Novedad:'+selectNovedades(data.idalumno, data.idnovedad)+'</label>';
    else
        html += '       <label for="ckbox-5-'+data.idalumno+'">Novedad:'+selectNovedades(data.idalumno,-1)+'</label>';
    html += '   </div>';
    html += '</fieldset>';
    
    return html;
}


function getArrayNovedades(){
       $.ajax({
            type : "GET",
            url : server + 'agent/get_all_novedades',        
            dataType : "json",
            data : {}
        }).done(function(response){
    
            if(response.state == 'ok'){
                for(var i in response.result){
                    novedadesArray.push(response.result[i]);
                    
                }
            }

        });
}


function selectNovedades(idalumno, idnovedad){
    var html = '';
    html = '<select name="select-novedades-'+idalumno+'" id="select-novedades-'+idalumno+'" onchange="updateStateStudent('+idalumno+',5)" >';

    html = html + '<option value="-1">...</option>';
    for(var i in novedadesArray){
        if ( novedadesArray[i].id==idnovedad)
            html = html + '<option value=' + novedadesArray[i].id + ' selected>' + novedadesArray[i].descripcion + '</option>';
        else
            html = html + '<option value=' + novedadesArray[i].id + '>' + novedadesArray[i].descripcion + '</option>';
    }
    html = html + '</select>';
    return html;   
}

function updateStateStudent(idstudent,idstate){

    if ($('#ckbox-'+idstate+'-'+idstudent).prop("checked")||(idstate=='5')){
        if(idstate=='5'){
            idnovedad   = $('#select-novedades-'+idstudent).val(); 
            desnovedad  = $('#select-novedades-'+idstudent+' option:selected').text();  
        }else{
            idnovedad   = 0;
            desnovedad  = '';
        }
        
        localizame();

        var alumnodata = {
                student     : idstudent,
                state       : idstate,
                idnovedad   : idnovedad,
                desnovedad  : desnovedad,
                latitud     : lat,
                longitud    : lng,
                insert      : 0,
                fecha       : getDateTime(),
                offline     : 0
            }

        $.ajax({
            type : "GET",
            url : server + 'agent/update_state_student',        
            dataType : "json",
            data : alumnodata
        }).done(function(response){
            
            if(response.state=='ok'){
                
            }else{
                errorUpdateStateStudent(alumnodata);
                //$('#ckbox-'+idstate+'-'+idstudent).attr("checked",false).checkboxradio("refresh");
                //alert("Error al intentar actualizar el estado del alumno, por favor intete de nuevo.");
            }
        }).fail(function(jqXHR, textStatus, errorThrown){
                errorUpdateStateStudent(alumnodata);
                //$('#ckbox-'+idstate+'-'+idstudent).attr("checked",false).checkboxradio("refresh");
                //alert("Error al intentar actualizar el estado del alumno, por favor intete de nuevo.");
        });     

        //cambiar estado sin importar si actualiza o no...
        for (i = 1; i <= 5; i++) { 
            if (i!=idstate)
                $('#ckbox-'+i+'-'+idstudent).attr("checked",false).checkboxradio("refresh");
        }
        $('#ckbox-'+idstate+'-'+idstudent).attr("checked",true).checkboxradio("refresh");
                
        var oldTheme = $('#fieldset-'+idstudent).attr('data-theme'); 
        var oldclass = 'ui-btn-up-'+oldTheme+' ui-body-d';

        var newTheme= 'a';
        var newclass= 'ui-btn-up-a ui-body-d';
        if ((idstate==2)||(idstate==4)){
            newTheme = 'e';
            newclass = 'ui-btn-up-e ui-body-d';
        }
        //console.log('#fieldset-'+idstudent+' tema viejo:'+oldTheme+' nuevo tema:'+newTheme);
        $('#fieldset-'+idstudent).attr('data-theme', newTheme);
        $('#fieldset-'+idstudent).trigger('refresh', newTheme);
                
        $('#fieldset-'+idstudent).find('a').toggleClass(oldclass + ' ui-btn-hover-d').toggleClass(newclass + ' ui-btn-hover-d');
        $('#fieldset-'+idstudent).trigger('collapse'); 
        $('#fieldset-'+idstudent).trigger('refresh');
        $('#student_stop').trigger('refresh');
                
        //getWayStop();

    }

}

function errorUpdateStateStudent(data){
    NotUpdateArray.push(data);
}


function  updateStateStudentError(id,alumnodata){
    alumnodata.offline = 1;
    $.ajax({
            type : "GET",
            url : server + 'agent/update_state_student',        
            dataType : "json",
            data : alumnodata
        }).done(function(response){
            if(response.state=='ok'){
               NotUpdateArray[id].insert = 1; 
            }
         }).fail(function(jqXHR, textStatus, errorThrown){
            // 
    });  
}

function  verifyErrorUpdate(){
    flagErrorUpdate = 'false';
    for(var i in NotUpdateArray){
        if(NotUpdateArray[i].insert===0){
            updateStateStudentError(i,NotUpdateArray[i]);
        }
    }
    for(var i = NotUpdateArray.length - 1; i >= 0; i--) {
        if(NotUpdateArray[i].insert===1) {
            NotUpdateArray.splice(i, 1);
        }
    
    }
    flagErrorUpdate = 'true';
}

function getDateTime(){
    var f       =   new Date();
    var year    =   f.getFullYear();
    var month   =   f.getMonth() + 1;
    var day     =   f.getDate();
    var hours   =   f.getHours();
    var minutes =   f.getMinutes();
    var seconds =   f.getSeconds();

    return year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds;
}

