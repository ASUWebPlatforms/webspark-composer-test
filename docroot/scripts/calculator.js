$ = jQuery
var asuinstate = 82752;
var asuoutstate = 137730;


//Pull School tuition costs from JSON file and update cost of tuition div on select
$(document).ready(function () {
    $.ajax({
        url: "/scripts/schools.JSON",
        dataType: "json",
        cache: false,
        success: function (data, textStatus, jqXHR) {
           // $('#schoolSelect').empty('Select a School');
            jQuery.each(data, function (k, v) {
                $('#schoolSelect').append($('<option>').text(v.name).attr('value', v.name).attr('instate', v.instate * 3).attr('outstate', v.outstate * 3).attr('cityid', v.cityid).attr('cityname', v.cityname).attr('cardid', v.cardid).attr('propname', v.propname).attr('video', v.video));
            });

            $('#schoolSelect').change(function () {
                instateValue = $('#schoolSelect option:selected').attr('instate');
                $('.instate').html('$' + instateValue);
                outstateValue = $('#schoolSelect option:selected').attr('outstate');
                $('.outstate').html('$' + outstateValue);
                compschool = $('#schoolSelect option:selected').attr('value');
                $('.compschool').html(compschool);
                cityname = $('#schoolSelect option:selected').attr('cityname');
                $('.cityname').html(cityname);
                cardid = $('#schoolSelect option:selected').attr('cardid');
                $('.cardsect').html('<img id="show" class="img-responsive" src="/sites/default/files/cards/card-' + cardid + '.png" />');                
                video = $('#schoolSelect option:selected').attr('video');
                $('.vidsect').html('<video width="80%" autoplay="autoplay" loop class="polaroid"><source src="/sites/default/files/cards/' + video + '" type="video/mp4"></video>');
                propername = $('#schoolSelect option:selected').attr('propname');
                $('.propname').html(propername);
                placeid = $('#schoolSelect option:selected').attr('cityid');



                //show scholarship amount as inputed by user and update total value
                $('#asuscholarship')
                    .keyup(function () {
                        var asuscholarvalue = $(this).val();
                        $('.asuscholardiv').text('- ' + asuscholarvalue);
                        $('.asuinstatetotal').text(asuinstate - asuscholarvalue);
                        $('.asuoutstatetotal').text(asuoutstate - asuscholarvalue);
                    })
                    .keyup();
                $('#scholarship')
                    .keyup(function () {
                        var scholarvalue = $(this).val();
                        $('.scholardiv').text('- ' + scholarvalue);
                        $('.instatetotal').text(instateValue - scholarvalue);
                        $('.outstatetotal').text(outstateValue - scholarvalue);
                    })
                    .keyup();


                //Annual Expenditure Data by family type
                $(document).ready(function () {
                    $.ajax({
                        type: "GET",
                        url: "https://api.c2er.org/costofliving/v3.0/api/api/GetTotalMonthlySpendingComparisonByHouseholdType?licenseeGuid=%7B139BC3CA-1B82-4209-BE04-5DA773DD3857%7D&indexType=1&fromPlace=19&toPlace=" + placeid,
                        dataType: "xml",
                        success: function (xml) {
                            var year = 27;
                            //Renter
                            $(xml).find('Table1:nth-child(4)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text().replace("$", "").replace(",", "");
                                var ToPlaceCost = $(this).find("ToPlaceCost").text().replace("$", "").replace(",", "");
                                $(".renter").html('+ ' + fromPlaceCost * year);
                                $(".comprenter").html('+ ' + ToPlaceCost * year);
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.rentervalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuinstate) - scholarval));
                                })
                                .keyup();
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.renteroutvalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuoutstate) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.comprentervalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(instateValue) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.comprenteroutvalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(outstateValue) - scholarval));
                                })
                                .keyup();
                            });
                            //Home Owner
                            $(xml).find('Table1:nth-child(3)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text().replace("$", "").replace(",", "");
                                var ToPlaceCost = $(this).find("ToPlaceCost").text().replace("$", "").replace(",", "");
                                $(".homeowner").html('+ ' + fromPlaceCost * year);
                                $(".comphomeowner").html('+ ' + ToPlaceCost * year);
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.homeownervalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuinstate) - scholarval));
                                })
                                .keyup();
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.homeowneroutvalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuoutstate) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.comphomeownervalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(instateValue) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.comphomeowneroutvalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(outstateValue) - scholarval));
                                })
                                .keyup();
                            });
                            //Family with Children Under 6
                            $(xml).find('Table1:nth-child(1)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text().replace("$", "").replace(",", "");
                                var ToPlaceCost = $(this).find("ToPlaceCost").text().replace("$", "").replace(",", "");
                                $(".fam1cost").html('+ ' + fromPlaceCost * year);
                                $(".compfam1cost").html('+ ' + ToPlaceCost * year);
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.familykidsvalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuinstate) - scholarval));
                                })
                                .keyup();
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.familykidsoutvalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuoutstate) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.compfamilykidsvalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(instateValue) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.compfamilykidsoutvalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(outstateValue) - scholarval));
                                })
                                .keyup();
                            });
                            //Family with Children 6-17
                            $(xml).find('Table1:nth-child(2)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text().replace("$", "").replace(",", "");
                                var ToPlaceCost = $(this).find("ToPlaceCost").text().replace("$", "").replace(",", "");
                                $(".oldfam1cost").html('+ ' + fromPlaceCost * year);
                                $(".compoldfam1cost").html('+ ' + ToPlaceCost * year);
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.familyteensvalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuinstate) - scholarval));
                                })
                                .keyup();
                                $("#asuscholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.familyteensoutvalue').text('$' + (parseInt(fromPlaceCost * year) + parseInt(asuoutstate) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.compfamilyteensvalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(instateValue) - scholarval));
                                })
                                .keyup();
                                $("#scholarship")
                                    .keyup(function () {
                                    var scholarval = $(this).val();
                                    $('.compfamilyteensoutvalue').text('$' + (parseInt(ToPlaceCost * year) + parseInt(outstateValue) - scholarval));
                                })
                                .keyup();
                            });
                        },
                        error: function () {
                            alert("An error occurred while processing XML file.");

                        }

                    });

                });
                
                
                
                //COL Data by item
                $(document).ready(function () {
                    $.ajax({
                        type: "GET",
                        url: "https://api.c2er.org/costofliving/v3.0/api/api/GetAveragePriceComparison?licenseeGuid=%7B139BC3CA-1B82-4209-BE04-5DA773DD3857%7D&indexType=1&fromPlace=19&toPlace=" + placeid,
                        dataType: "xml",
                        success: function (xml) {
                            //Apartment Rent
                            $(xml).find('Table1:nth-child(27)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text();
                                var ToPlaceCost = $(this).find("ToPlaceCost").text();
                                $("#apartment").html(fromPlaceCost);
                                $("#compapartment").html(ToPlaceCost);
                            });
                            //Home Price
                            $(xml).find('Table1:nth-child(28)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text();
                                var ToPlaceCost = $(this).find("ToPlaceCost").text();
                                $("#homeprice").html(fromPlaceCost);
                                $("#comphomeprice").html(ToPlaceCost);
                            });
                            //Total Energy
                            $(xml).find('Table1:nth-child(29)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text();
                                var ToPlaceCost = $(this).find("ToPlaceCost").text();
                                $("#energy").html(fromPlaceCost);
                                $("#compenergy").html(ToPlaceCost);
                            });
                            //Gasoline
                            $(xml).find('Table1:nth-child(32)').each(function () {
                                var fromPlaceCost = $(this).find("FromPlaceCost").text();
                                var ToPlaceCost = $(this).find("ToPlaceCost").text();
                                $("#gas").html(fromPlaceCost + '/gal');
                                $("#compgas").html(ToPlaceCost + '/gal');
                            });
                        },
                        error: function () {
                            alert("An error occurred while processing XML file.");

                        }

                    });

                });
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('FAILED to get  JSON from AJAX call' + jqXHR + textStatus + errorThrown);

        }
    });
});

//ASU Column
$(document).ready(function () {
    $('.asuinstate').html('$' + asuinstate);
    $('.asuoutstate').html('$' + asuoutstate);
});

//Radio Buttons show/hide
var elems = $(':radio.minimal');
elems.change(function () {
    var v = $(elems).filter(':checked').val();
    var continer = $('.radio-content');
    //Hide all
    continer.hide();
    continer.filter('[data-radio=' + v + ']').show();
}).change();
