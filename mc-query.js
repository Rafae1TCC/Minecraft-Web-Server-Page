const util = require('minecraft-server-util');

util.queryFull('127.0.0.1', 7697)
    .then((response) => {
        console.log(response);
        // Muestra los jugadores y otra información del servidor
    })
    .catch((error) => {
        console.error(error);
    });