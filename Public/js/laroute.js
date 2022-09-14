(function () {
    var module_routes = [
    {
        "uri": "delete-note\/{thread_id}",
        "name": "deletenotes.delete"
    }
];

    if (typeof(laroute) != "undefined") {
        laroute.add_routes(module_routes);
    } else {
        contole.log('laroute not initialized, can not add module routes:');
        contole.log(module_routes);
    }
})();