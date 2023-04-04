function rechercherLieu(){
    let $search =$('#rechercher').val();
    let $lieu_ville = $('#lieu_ville');
    let $index = $lieu_ville.val();
    let $villeRecherchee =$lieu_ville.children('option[value='+$index+']');
    let $btnaj=$('#btnAj');
    $.ajax({
        url :"https://api-adresse.data.gouv.fr/search/?q="+$search+"&postcode="+$villeRecherchee[0].outerText,
        method :"GET",
    }).done((donnees) =>{
        $('#label').text(donnees.features[0].properties.label);
        $('#lieu_rue').val(donnees.features[0].properties.name);
        $('#lieu_longitude').val(donnees.features[0].geometry.coordinates[0]);
        $('#lieu_latitude').val(donnees.features[0].geometry.coordinates[1]);
        $('#question').text('S\'agit t\'il bien de l\'adresse que vous recherchez ?')
        $btnaj.removeAttr('hidden');
    })
}

function rechercherVille(){
    let $input =$('#rechercherVille');
    let $searchCity=$input.val();
    let $listeVille=$('#listeVille');
    $('.option').remove();
    $input.attr('aria-expanded',true);
    $.ajax({
        url:"https://geo.api.gouv.fr/communes?nom="+$searchCity,
        method: "GET",
    }).done((donnees)=>{
        console.log(donnees)
        for (let i = 0; i < donnees.length; i++) {
            for (let j = 0; j < donnees[i].codesPostaux.length; j++) {
                let $element = $(document.createElement('option'));
                $element.attr('class','option');
                $element.attr('role','option');
                $element.val(donnees[i].nom+"-"+donnees[i].codesPostaux[j]);
                $element.text(donnees[i].nom+"-"+donnees[i].codesPostaux[j]);
                $listeVille.append($element);
            }

        }
        let $select =$('#listeVille').val().split("-")
        console.log($select);
        $('#ville_nom').val($select[0])
        $('#ville_codePostal').val($select[1]);
    })
}



