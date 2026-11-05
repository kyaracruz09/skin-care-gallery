document.addEventListener("DOMContentLoaded", function () {

    const imagens = document.querySelectorAll(".produto-card img");

    imagens.forEach(function (imagem) {

        imagem.addEventListener("error", function () {
            imagem.alt = "Imagem do produto indisponível";
        });

    });

});