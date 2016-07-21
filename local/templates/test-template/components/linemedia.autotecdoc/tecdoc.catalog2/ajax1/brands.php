<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__); ?>

<?

global $APPLICATION;

$tecdoc_path = $arParams['PATH_TO_TECDOC'] ?: '/auto/tecdoc/';
$ajax_path = str_replace('/index.php', '', $APPLICATION->GetCurPage()) . '/';
$page = $APPLICATION->GetCurPage();

?>
	<script type="text/javascript">
		var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
		var contemporaryYear = <?php echo $arParams['CONTEMPORARY_YEAR']; ?>
	</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>
	<div id="tecdoc-wrapper-id">
		<select id="brand-select-id">
			<option value=""><?=GetMessage('LM_AUTO_TECDOC_BRAND')?></option>
			<? foreach ($arResult['BRANDS'] as $letter => $brands) { ?>
				<? foreach ($brands as $brand) { ?>
					<option value="<?= htmlspecialcharsEx($brand['manuId']) ?>"><?= htmlspecialcharsEx($brand['manuName']) ?></option>
				<? } ?>
			<? } ?>
		</select>
		<select id="model-select-id">
			<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODEL')?></option>
		</select>
		<select id="modification-select-id">
			<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODIFICATION')?></option>
		</select>
		<a href="/auto/tecdoc/" id="btn-tecdoc-ajax-submit" class="btn btn-default"><?=GetMessage('LM_AUTO_TECDOC_SEARCH')?></a>
	</div>
<? if (strstr($arResult['URL'], '/auto/tecdoc/')) { ?>
	<script>
		var arrSel = window.location.pathname.replace('<?=$tecdoc_path?>', '').split('/');
		function loadTecdocSelect() {
			//if (arrSel[1].length > 0) {
			$('#brand-select-id').val(arrSel[1]).change();
			$('#model-select-id').val(arrSel[2]).change();
			$('#modification-select-id').val(arrSel[3]).change();
			//};
		}
		loadTecdocSelect();
	</script>
<? } else { ?>
	<script>
		var arrSel = [];
	</script>
<? } ?>

	<script>
	$(document).ready(function() {
		var $items = {};
		var urlDetail = '';
		var defaultUrlDetail = '';

		// Выбор бренда.
		$('#brand-select-id').on('change', function() {
			var self = $('#brand-select-id');
			var brandID = self.val();

			//$('#btn-tecdoc-ajax-submit').attr('href', '/auto/tecdoc/'+brandID+'/');

			if (brandID !== '') {
				$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>'+brandID+'/');
				$.ajax({
					'url': '<?=$ajax_path?>' + brandID + '/',
					'beforeSend': function() {
						$('#model-select-id').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODEL')?></option>').trigger('refresh');
						$('#modification-select-id').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODIFICATION')?></option>').trigger('refresh');
						$('#modification-select-id').nextAll('.selectbox, select').remove();
						$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>'+brandID+'/');
					},
					'success': function(response) {
						//$('#tecdoc-wrapper-id').append(response);
						$('#model-select-id').empty().append(response).trigger('refresh');
						if (typeof arrSel[2] !== "undefined") {
							if (arrSel[2].length > 0) {
								$('#model-select-id').val(arrSel[2]).change().trigger('refresh');
							};
						};
						//$('#tecdoc-wrapper-id select').selectbox();
					}
				});
			} else {
				$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>');
				$('#model-select-id').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODEL')?></option>').trigger('refresh');
				$('#modification-select-id').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODIFICATION')?></option>').trigger('refresh');
				$('#modification-select-id').nextAll('.selectbox, select').remove();
				//$('#tecdoc-wrapper-id .btn').addClass('disabled');
			};
		})
			.change();

		// Выбор модели.
		$('#model-select-id').on('change', function() {

			var self = $('#model-select-id');
			var modelID = self.val();
			var brandID = $('#brand-select-id').val();

			//$('#btn-tecdoc-ajax-submit').attr('href', '/auto/tecdoc/'+brandID+'/'+modelID+'/');

			if (modelID !== '') {
				$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>'+brandID+'/'+modelID+'/');
				$.ajax({
					'url': '<?=$ajax_path?>' + brandID + '/' + modelID + '/',
					'beforeSend': function() {
						self.nextAll('select').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODIFICATION')?></option>').trigger('refresh');
						$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>'+brandID+'/'+modelID+'/');
					},
					'success': function(response) {
						//$('#tecdoc-wrapper-id').append(response);
						$('#modification-select-id').empty().append(response).trigger('refresh');
						if (typeof arrSel[3] !== "undefined") {
							if (arrSel[3].length > 0) {
								$('#modification-select-id').val(arrSel[3]).change().trigger('refresh');
							};
						};
						//$('#tecdoc-wrapper-id select').selectbox();
					}
				});
			} else {
				if ( brandID !== '') {
					$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>'+brandID+'/');
				}
				self.nextAll('select').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_MODIFICATION')?></option>').trigger('refresh');
				$('#modification-select-id').nextAll('.selectbox, select').remove();
				//$('#tecdoc-wrapper-id .btn').addClass('disabled');
			};
		})
			.change();

		// Выбор модификации.
		$('#modification-select-id').on('change', function() {
			var self = $('#modification-select-id');
			var brandID = $('#brand-select-id').val();
			var modelID = $('#model-select-id').val();
			var modificationID = self.val();
			urlDetail = '<?=$tecdoc_path?>' + brandID + '/' + modelID + '/' + modificationID + '/';
			defaultUrlDetail = urlDetail;

			//$('#btn-tecdoc-ajax-submit').attr('href', '/auto/tecdoc/' + brandID + '/' + modelID + '/' + modificationID + '/');

			if (modificationID !== '') {
				$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>' + brandID + '/' + modelID + '/' + modificationID + '/');
				$.ajax({
					'url': '/ajax/tecdoc/' + brandID + '/' + modelID + '/' + modificationID + '/',
					'dataType': 'json',
					'beforeSend': function() {
						self.nextAll('select').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_SELECT')?></option>').trigger('refresh');
						$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>' + brandID + '/' + modelID + '/' + modificationID + '/');
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
							$select.append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_SELECT')?></option>');
							$select.append($options);

							self.next('.btn').remove();
							$('#tecdoc-wrapper-id').append($select);
							$('#tecdoc-wrapper-id').append('<a href="'+urlDetail+'" class="btn btn-default" id="btn-tecdoc-ajax-submit"><?=GetMessage('LM_AUTO_TECDOC_SEARCH')?></a>');

							// автоподгрузка селектов с категориями
							if (typeof arrSel[4] !== "undefined") {
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
											};
										};
									});
									$arrParts.reverse();
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
				if ( modelID !== '') {
					$('#btn-tecdoc-ajax-submit').attr('href', '<?=$tecdoc_path?>' + brandID + '/' + modelID + '/');
				}
				self.nextAll('select').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_SELECT')?></option>').trigger('refresh');
				$('#modification-select-id').nextAll('.selectbox, select').remove();
				//$('#tecdoc-wrapper-id .btn').addClass('disabled');
			}
		})
			.change();

		// Выбор категории.
		$('.parts-group').on('change', function() {
			var self = $(this);
			var parentID = self.val();
			var level = self.data('level');
			self.prev('.btn').remove();
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
					$select.append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_SELECT')?></option>');
					$select.append($options);
					$('#tecdoc-wrapper-id').append($select);
					$('#tecdoc-wrapper-id select').selectbox();
					$('#tecdoc-wrapper-id').append('<a href="'+urlDetail+'" class="btn btn-default" id="btn-tecdoc-ajax-submit"><?=GetMessage('LM_AUTO_TECDOC_SEARCH')?></a>');
				} else {
					urlDetail = urlDetail + parentID + '/';
					$('#tecdoc-wrapper-id').append('<a href="'+urlDetail+'" class="btn btn-default" id="btn-tecdoc-ajax-submit"><?=GetMessage('LM_AUTO_TECDOC_SEARCH')?></a>');
					//$('#tecdoc-wrapper-id .btn').attr('href', urlDetail).removeClass('disabled');
					urlDetail = defaultUrlDetail;
				}
			} else {
				self.nextAll('select').empty().append('<option value=""><?=GetMessage('LM_AUTO_TECDOC_SELECT')?></option>').trigger('refresh');
				$('#tecdoc-wrapper-id').append('<a href="'+urlDetail+'" class="btn btn-default" id="btn-tecdoc-ajax-submit"><?=GetMessage('LM_AUTO_TECDOC_SEARCH')?></a>');
			}
		})
			.change();

	});
	</script>


<? include(dirname(__FILE__) . '/footer.php'); ?>