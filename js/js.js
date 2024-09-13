function createSortTable()
{
	$('table[data-table-sort="true"]').each(function(index, element) {
        var thisTable = $(this);
		var self = thisTable.attr('data-self-name');
		var originalURL = document.location.toString();
		var arr0 = originalURL.split('#');
		originalURL = arr0[0];
		var arr1 = originalURL.split('?');
		originalURL = arr1[0];
		var args = arr1[1] || '';
		var argArray = args.split('&');
		var queryObject = {};
		for(var i in argArray)
		{
			var arr2 = argArray[i].split('=');
			if(arr2[0] != '')
			{
				queryObject[arr2[0]] = arr2[1]; 
			}
		}
		var currentOrderMethod = queryObject.ordermethod || '';
		var lastColumn = queryObject.orderby || '';
		thisTable.find('td.col-sort').each(function(index, element) {
			var thisCel = $(this);
			var columnName = thisCel.attr('data-col-name');
			if(lastColumn == columnName)
			{
				
				if(currentOrderMethod == 'asc')
				{
					queryObject.ordermethod = 'desc'
					thisCel.attr('data-order-method', 'asc');
				}
				else if(currentOrderMethod == 'desc')
				{
					queryObject.ordermethod = 'asc'
					thisCel.attr('data-order-method', 'desc');
				}
				else
				{
					queryObject.ordermethod = 'asc'
					thisCel.attr('data-order-method', 'desc');
				}
			}
			else
			{
				queryObject.ordermethod = 'asc'
			}
			queryObject.orderby = columnName;
			var arr3 = [];
			for(var j in queryObject)
			{
				arr3.push(j+'='+queryObject[j]);
			}
			var args3 = arr3.join('&');
			var finalURL = originalURL + '?' + args3;
			thisCel.find(' > a').attr('href', finalURL);
        });
    });
}

function createPagination()
{
	$('[data-pagination="true"]').each(function(index, element) {
        var thisPagination = $(this);
		var self = thisPagination.attr('data-self-name');
		var maxRecord = parseInt(thisPagination.attr('data-max-record') || '0');
		var recordPerPage = parseInt(thisPagination.attr('data-record-per-page') || '10');
		if(recordPerPage < 1)
		{
			recordPerPage = 1;
		}
		if(currentOffset < 0)
		{
			currentOffset = 0;
		}
		if(maxRecord < 0)
		{
			maxRecord = 0;
		}
		var originalURL = document.location.toString();
		var arr0 = originalURL.split('#');
		originalURL = arr0[0];
		var arr1 = originalURL.split('?');
		originalURL = arr1[0];
		var args = arr1[1] || '';
		var argArray = args.split('&');
		var queryObject = {};
		for(var i in argArray)
		{
			var arr2 = argArray[i].split('=');
			if(arr2[0] != '')
			{
				queryObject[arr2[0]] = arr2[1]; 
			}
		}
		
		var currentOffset = parseInt(queryObject.offset || '0');
		
		var numPage = Math.floor(maxRecord/recordPerPage);
		if(maxRecord%recordPerPage)
		{
			numPage++;
		}
		
		
		// nomal pagination
		/*
		for(var j = 0, k=1; j<numPage; j++, k++)
		{
			var offset = (j*recordPerPage);
			queryObject.offset = offset;
			var arr3 = [];
			for(var l in queryObject)
			{
				arr3.push(l+'='+queryObject[l]);
			}
			var args3 = arr3.join('&');
			var finalURL = originalURL + '?' + args3;
			var la = $('<a href="'+finalURL+'">'+k+'</a> ');
			if(offset == currentOffset)
			{
				la.addClass('page-selected');
			}
			thisPagination.append(la);
		}
		*/
		
		var currentPage = (currentOffset/recordPerPage)+1;
		var maxPage = numPage;
		var firstPage = currentPage-3;	
		var lastPage = currentPage+2;	
		
		if(firstPage < 0)
		{
			firstPage = 0;
		}
		if(lastPage > maxPage)
		{
			lastPage = maxPage;
		}
		
		if(firstPage > 1)
		{
			// create firs
			var j = 0, k = 1;
			{
				var offset = (j*recordPerPage);
				queryObject.offset = offset;
				var arr3 = [];
				for(var l in queryObject)
				{
					arr3.push(l+'='+queryObject[l]);
				}
				var args3 = arr3.join('&');
				var finalURL = originalURL + '?' + args3;
				var la = $('<a href="'+finalURL+'">&laquo;</a> ');
				thisPagination.append(la);
			}
		}
		if(firstPage > 0)
		{
			// create previous
			var j = firstPage+1, k = firstPage+2;
			{
				var offset = (j*recordPerPage);
				queryObject.offset = offset;
				var arr3 = [];
				for(var l in queryObject)
				{
					arr3.push(l+'='+queryObject[l]);
				}
				var args3 = arr3.join('&');
				var finalURL = originalURL + '?' + args3;
				var la = $('<a href="'+finalURL+'">&lt;</a> ');
				thisPagination.append(la);
			}
		}
		
		for(var j = firstPage, k=firstPage+1; j<lastPage; j++, k++)
		{
			var offset = (j*recordPerPage);
			queryObject.offset = offset;
			var arr3 = [];
			for(var l in queryObject)
			{
				arr3.push(l+'='+queryObject[l]);
			}
			var args3 = arr3.join('&');
			var finalURL = originalURL + '?' + args3;
			var la = $('<a href="'+finalURL+'">'+k+'</a> ');
			if(offset == currentOffset)
			{
				la.addClass('page-selected');
			}
			thisPagination.append(la);
		}
		

		if(currentPage < maxPage-2)
		{
			// create previous
			var j = currentPage, k = currentPage+1;
			{
				var offset = (j*recordPerPage);
				queryObject.offset = offset;
				var arr3 = [];
				for(var l in queryObject)
				{
					arr3.push(l+'='+queryObject[l]);
				}
				var args3 = arr3.join('&');
				var finalURL = originalURL + '?' + args3;
				var la = $('<a href="'+finalURL+'">&gt;</a> ');
				thisPagination.append(la);
			}
		}
		if(currentPage < maxPage-3)
		{
			// create last
			var j = maxPage-1, k = maxPage;
			{
				var offset = (j*recordPerPage);
				queryObject.offset = offset;
				var arr3 = [];
				for(var l in queryObject)
				{
					arr3.push(l+'='+queryObject[l]);
				}
				var args3 = arr3.join('&');
				var finalURL = originalURL + '?' + args3;
				var la = $('<a href="'+finalURL+'">&raquo;</a> ');
				thisPagination.append(la);
			}
		}

		
    });
}
