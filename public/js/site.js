/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function(){
    $('img').each(function(){
        $(this).load(function(){
            resetEqualHeights();
        });
    }); 
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
