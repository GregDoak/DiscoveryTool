var start = 0;
var limit = 100;
var fetchMoreResults = true;
var showFacets = true;
var pixelsToBottom = 100;
$(document).ready(function () {
    loadMoreResults();
    $(window).scroll(function () {
        if (fetchMoreResults) {
            if ($(window).scrollTop() + $(window).height() >
                $(document).height() - pixelsToBottom) {
                loadMoreResults();
            }
            ;
        }
    });
});

function loadMoreResults() {
    $.get("results.json", {
        q: $("#q").val(),
        start: start,
        limit: limit
    }, function (data) {
        if (data.code == 200) {
            if (data.facets && showFacets) {
                for (var index in data.facets) {
                    var html = '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += index;
                    html += '</div>';
                    html += '<ul class="list-group">';
                    for (facet in data.facets[index]) {
                        html += '<li class="list-group-item">' + facet + '<span class="pull-right"><span class="badge badge-impo">' + data.facets[index][facet] + '</span></span></li>';
                    }
                    html += "</ul>";
                    html += '</div>';
                    $('#facetReturns').append(html);
                    showFacets = false;
                }
            }
            if (data.count > 0) {
                $('#countOfResults').text(data.count + ' ' + plural('result', data.count, 's') + ' found.');
                for (var doc in data.data) {
                    var document = data.data[doc];
                    var searchReturns = '<div class="well well-sm" style="margin:5px; padding:0px;">' +
                        '<div class="media"> ' +
                        '<div class="media-left"> ' +
                        '<a href="#"> ' +
                        '<img class="media-object" src="' + document.thumbnail + '" alt="..."> ' +
                        '</a> ' +
                        '</div> ' +
                        '<div class="media-body"> ' +
                        '<h3 class="media-heading">' + document.title + '</h3>' +
                        '<h5 class="media-heading">' + document.subtitle + '</h5>' +
                        document.summary +
                        '</div> ' +
                        '</div>' +
                        ' </div>';
                    $('#searchReturns').append(searchReturns);
                }
            } else {
                var html = '<div><h1>No results found</h1></div>';
                //$('#searchReturns').append(html);
                fetchMoreResults = false;
            }
        }
    });

    start = start + limit;
}

function plural(single, count, suffix) {
    return (count == 1) ? single : single + suffix;
}