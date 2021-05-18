function updateBorders(color) {
    var hexColor = "transparent";
    if(color) {
        hexColor = color.toHexString();
    }
    $("#docs-content").css("border-color", hexColor);
}

$(function() {

$("#full").spectrum({
    allowEmpty:true,
    color: "#FFF",
    showInput: true,
    containerClassName: "full-spectrum",
    showInitial: true,
    showPalette: true,
    showSelectionPalette: true,
    showAlpha: true,
    maxPaletteSize: 10,
    move: function (color) {
        updateBorders(color);
        $("#full").attr("value",tinycolor(color).toHexString(color));
    },
    show: function () {
    },
    beforeShow: function () {
    },
    hide: function (color) {
        updateBorders(color);
    },

    palette: [
        ["#63b359", "#2c9f67", "#509fc9", "#5885cf", "#9062c0"],
        ["#d09a45", "#e4b138", "#ee903c", "#dd6549", "#cc463d"],
    ]
});

});
