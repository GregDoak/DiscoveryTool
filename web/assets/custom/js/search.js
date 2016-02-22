var start = 0;
var limit = 100;
var pixelsToBottom = 100;
$(document).ready(function () {
    loadMoreResults();
    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() >
            $(document).height() - pixelsToBottom) {
            loadMoreResults();
        }
        ;
    });
});

function loadMoreResults() {
    $.get("results.json", {
        q: $("#q").val(),
        start: start,
        limit: limit
    }, function (data) {
        if (data.code == 200) {
            if (data.facets) {
                for (var index in data.facets) {
                    var html = '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += index;
                    html += '</div>';
                    html += '<ul class="list-group">';
                    for (facet in data.facets[index]) {
                        html += '<li class="list-group-item">' + facet + '<span class="pull-right"><span class="badge">' + data.facets[index][facet] + '</span></span></li>';
                    }
                    html += "</ul>";
                    html += '</div>';
                    $('#facetReturns').append(html);
                }
            }
            if (data.count > 0) {
                $('#countOfResults').text(data.count + ' ' + plural('result', data.count, 's') + ' found.');
                for (var index in data.data) {
                    var searchReturns = '<div><h1>' + data.data[index].title + '</h1></div>';
                    $('#searchReturns').append(searchReturns);
                }
            } else {
                var html = '<div><h1>No results found</h1></div>';
                $('#searchReturns').append(html);
            }
        }
    });

    start = start + limit;
}

function plural(single, count, suffix) {
    return (count == 1) ? single : single + suffix;
}