<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__); ?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
    var contemporaryYear = <?php echo $arParams['CONTEMPORARY_YEAR']; ?>
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>
 <div id="tecdoc-wrapper-id">
  <select id="brand-select-id">
   <option value="">- выберите -</option>
   <? foreach ($arResult['BRANDS'] as $letter => $brands) { ?>
    <? foreach ($brands as $brand) { ?>
     <option value="<?= htmlspecialcharsEx($brand['manuId']) ?>"><?= htmlspecialcharsEx($brand['manuName']) ?></option>
    <? } ?>
   <? } ?>
  </select>
</div>
<? if (strstr($arResult['URL'], '/auto/tecdoc/')) { ?>
	<script>
	var arrSel = window.location.pathname.replace('/auto/tecdoc', '').split('/');
	function loadTecdocSelect() {
		if (arrSel[1].length > 0) {
			$('#brand-select-id').val(arrSel[1]).change();
		};
	}
	loadTecdocSelect();
	</script>
<? } ?>
 
<script>
 $(document).ready(function() {
  var $items = {};
  var urlDetail = '';
  var defaultUrlDetail = '';
   
  // Выбор бренда.
  $('#brand-select-id').live('change', function() {
	var self = $('#brand-select-id');
	var brandID = self.val();

	if (brandID !== '') {   
   $.ajax({
    'url': '/ajax/tecdoc/' + brandID + '/',
    'beforeSend': function() {
	 self.nextAll('.selectbox').remove();
     $('#model-select-id, #modification-select-id, .parts-group').remove();
     self.nextAll('.btn').remove();
    },
    'success': function(response) {
     $('#tecdoc-wrapper-id').append(response);
		if (typeof arrSel !== "undefined") {
			if (arrSel[2].length > 0) {
				$('#model-select-id').val(arrSel[2]).change();
			};
		};
     $('#tecdoc-wrapper-id select').selectbox();
    }
   });
   } else {
	self.nextAll('.selectbox, select, .btn').remove();
   };
  })
  .change();
  
  // Выбор модели.
  $('#model-select-id').live('change', function() {

	var self = $('#model-select-id');
	var modelID = self.val();
	var brandID = $('#brand-select-id').val();
   
   if (modelID !== '') {
   $.ajax({
    'url': '/ajax/tecdoc/' + brandID + '/' + modelID + '/',
    'beforeSend': function() {
	 $('#model-select-id').nextAll('.selectbox').remove();
     $('#modification-select-id').remove();
     $('.parts-group').remove();
	 $('#model-select-id').nextAll('.btn').remove();
    },
    'success': function(response) {
     $('#tecdoc-wrapper-id').append(response);
		if (typeof arrSel !== "undefined") {
			if (arrSel[3].length > 0) {
				$('#modification-select-id').val(arrSel[3]).change();
			};
		};
     $('#tecdoc-wrapper-id select').selectbox();
    }
   });
   } else {
	self.nextAll('.selectbox, select, .btn').remove();
   };
  })
  .change();
  
  // Выбор модификации.
  $('#modification-select-id').live('change', function() {
	var self = $('#modification-select-id');
   var brandID = $('#brand-select-id').val();
   var modelID = $('#model-select-id').val();
   var modificationID = self.val();
   urlDetail = '/auto/tecdoc/' + brandID + '/' + modelID + '/' + modificationID + '/';
   defaultUrlDetail = urlDetail;
   
   if (modificationID !== '') {
   $.ajax({
    'url': '/ajax/tecdoc/' + brandID + '/' + modelID + '/' + modificationID + '/',
    'dataType': 'json',
    'beforeSend': function() {
	 self.nextAll('.selectbox').remove();
     $('.parts-group').remove();
	 self.nextAll('.btn').remove();
    },
    'success': function(response) {
     $items = response;
	 
     var $select = $('<select data-level="0" class="parts-group"></select>');
     var $options = [];
     for (var i in $items) {
      var item = $items[i];
      if (!item['parentNodeId']) {
       $options.push($('<option value="' + item['assemblyGroupNodeId'] + '" >' + item['assemblyGroupName'] + '</option>'));
      }
     }
     
     if ($options.length > 0) {
		  $select.append('<option value="">- выберите -</option>');
		  $select.append($options);
		  
		  $('#tecdoc-wrapper-id').append($select);
		  
			// автоподгрузка селектов с категориями
			if (typeof arrSel !== "undefined") {
				if (arrSel[4].length > 0) {
					 var part = parseInt(arrSel[4]);
					 var k = 0;
					 var $arrParts = [];
					 $arrParts[0] = part;
					 $.each ($items, function(){
						 for (var i in $items) {
						  var item = $items[i];
						  if ((item['assemblyGroupNodeId'] == part) && (item['parentNodeId'] !== null)) {
							k++;
							$arrParts[k] = item['parentNodeId'];
							part = item['parentNodeId'];
							console.log($arrParts);
						  };
						 };
					 });
					 $arrParts.reverse();
					 console.log($arrParts);
					 //$('.parts-group').val($arrParts[0]).change();
					 for (var i in $arrParts) {
						var ind = parseInt(i);
						$('.parts-group[data-level=' + ind + '] option[value="' + $arrParts[ind] + '"]').attr('selected', 'selected');
						$('.parts-group[data-level=' + ind + ']').val($arrParts[ind]).trigger('change').trigger('refresh');
					 };
				};
			};
		 }
		$('#tecdoc-wrapper-id select').selectbox();
    }
   });
   } else {
	self.nextAll('.selectbox, select, .btn').remove();
   }
  })
  .change();
  
  // Выбор категории.
  $('.parts-group').live('change', function() {
   var self = $(this);
   var parentID = self.val();
   var level = self.data('level');
   self.nextAll('.parts-group, .selectbox, .btn').remove();
   urlDetail = defaultUrlDetail;
   
   if (parentID !== '') {
   
   var $select = $(' <select data-level="' + (level + 1) + '" class="parts-group"></select> ');
   var $options = [];
   
   for (var i in $items) {
    var item = $items[i];
    if (item['hasChilds'] && item['parentNodeId'] == parentID) {
     $options.push($('<option value="' + item['assemblyGroupNodeId'] + '" >' + item['assemblyGroupName'] + '</option>'));
    }
   }
   
   if ($options.length > 0) {
    $select.append('<option value="">- выберите -</option>');
    $select.append($options);
    $('#tecdoc-wrapper-id').append($select);
    $('#tecdoc-wrapper-id select').selectbox();
   } else {
	urlDetail = urlDetail + parentID + '/';
	$('#tecdoc-wrapper-id').append('<a href="'+urlDetail+'" class="btn btn-red"><i class="icon-custom-search"></i> Поиск</a>');
	urlDetail = defaultUrlDetail;
   }
   } else {
	self.nextAll('.selectbox, select, .btn').remove();
   }
  })
  .change();

 });
</script>
 

<? include(dirname(__FILE__) . '/footer.php'); ?>