/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function(){
    onImageReady("img");
});

$(window).resize(function(){
    resetEqualHeights();
});

function resetEqualHeights() {
    $('.image').each(function(){
        $(this).css("height", "auto")
    });
    $('.image').equalHeights();
}

function onImageReady(selector) {
    var list;

    // If given a string, use it as a selector; else use what we're given
    list = typeof selector === 'string' ? $(selector) : selector;

    // Hook up each image individually
    list.each(function(index, element) {
        if (element.complete) {
            // Already loaded, fire the handler (asynchronously)
            setTimeout(function() {
//                element.equalHeights();
                resetEqualHeights();
            }, 0); // Won't really be 0, but close
        }
        else {
            // Hook up the handler
            $(element).bind('load', function(){
//                $(this).equalHeights();
                resetEqualHeights();
            });
        }
    });
}