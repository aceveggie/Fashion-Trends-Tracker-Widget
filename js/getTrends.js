function myFunction()
{
    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.post("http://localhost/wordpress/wp-content/plugins/ContentRotator/includes/ContentRotator.php", data, function(response){
        alert('Got this from the server: ' + response);
    });
});