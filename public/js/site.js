/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(window).bind("load", function() { 
    $('.image').equalHeights();
});

$(window).resize(function(){
    $('.image').each(function(){
        $(this).css("height", "auto")
    });
    $('.image').equalHeights();
});