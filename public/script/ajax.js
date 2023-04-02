let $ville = $('#sortie_form_ville');
// let $token=$('#sortie_form__token');
let $dateDebut=$('#sortie_form_dateHeureDebut');
let $dateLimiteInscription=$('#sortie_form_dateLimiteInscription');
let $nom=$('#sortie_form_nom');
let $duree=$('#sortie_form_duree');
let $nbMaxInscrit=$('#sortie_form_nbInscriptionsMax');
let $infosSortie=$('#sortie_form_infosSortie');

$ville.change(function(){
    let $form = $(this).closest('form');
    let data = {};
    data[$ville.attr('name')] = $ville.val();
    // data[$token.attr('name')]=$token.val();
    data[$dateDebut.attr('name')]=$dateDebut.val();
    data[$dateLimiteInscription.attr('name')]=$dateLimiteInscription.val();
    data[$nom.attr('name')]=$nom.val();
    data[$duree.attr('name')]=$duree.val();
    data[$nbMaxInscrit.attr('name')]=$nbMaxInscrit.val();
    data[$infosSortie.attr('name')]=$infosSortie.val();

    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : data,
        complete: function(html){
            $('#sortie_form_lieu').replaceWith($(html.responseText).find('#sortie_form_lieu'));
        }
    });
});