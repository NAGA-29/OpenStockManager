        // const chartdata = JSON.parse({{$data4chart_json}});
        const unescapeHtml = function(target) {
          if (typeof target !== 'string') return target;

          var patterns = {
              '&lt;'   : '<',
              '&gt;'   : '>',
              '&amp;'  : '&',
              '&quot;' : '"',
              '&#x27;' : '\'',
              '&#039;' : '\'',
              '&#x60;' : '`'
          };

          return target.replace(/&(lt|gt|amp|quot|#x27|#039|#x60);/g, function(match) {
              return patterns[match];
          });
      };
      // var r = unescapeHtml( "{{ $data4chart_json }}" );
      let data4chart = JSON.parse( unescapeHtml( "{{ $data4chart_json }}" ) );
      // console.log(data4chart);
