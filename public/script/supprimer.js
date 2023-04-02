function supprimer(sortie){
    if (window.confirm("Voulez-vous supprimer cette sortie ?")) {
        window.location.assign('/sortie/supprimer/'+sortie);
    }
}
