$("#checkall").change(function () {
    var checked = $(this).is(":checked");
    if (checked ) {
        $(".check-it").each(function () {
            $(this).prop("checked", true);
        });

    } else {
        $(".check-it").each(function () {
            $(this).prop("checked", false);
        });
    }
});
$(document).ready(function() {
    // Listener per ciascuna checkbox
    $(".check-it").change(function() {
        var email = $(this).data("email"); // Recupera l'email associata

        if ($(this).is(":checked")) {
            console.log("Checkbox per l'email " + email + " è selezionata.");
            // Esegui qui la tua chiamata AJAX, se necessario
            $.ajax({
                url: "user-list.php",
                method: "POST",
                data: {
                    selected_user: email,
                    change_role: 1 // Indica l'azione di selezione
                },
                success: function(response) {
                    console.log("Ruolo aggiornato per: " + email);
                    window.location.href="user-list.php"
                },
                error: function() {
                    alert("Errore durante la connessione al server.");
                }
            });
        } else {
            console.log("Checkbox per l'email " + email + " è deselezionata.");
            // Chiamata AJAX per deselezionare, se necessario
            $.ajax({
                url: "user-list.php",
                method: "POST",
                data: {
                    selected_user: email,
                    change_role: 2 // Indica l'azione di deselezione
                },
                success: function(response) {
                    console.log("Ruolo rimosso per: " + email);
                    window.location.href="user-list.php"
                },
                error: function() {
                    alert("Errore durante la connessione al server.");
                }
            });
        }
    });

    // Opzione per selezionare/deselezionare tutte le checkbox
    
});