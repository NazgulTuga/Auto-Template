teste
=====
	require_once($path.'core/scripts/TobjAutoTemplate.class.php');
	$tblAutoTpl = new TobjAutoTemplate();
	$_SESSION['path'] 		= $path;
	$_SESSION['TplObject'] 	= $tblAutoTpl;
	
	/* TABLE CLASS */
	require_once($path.'core/db/dbTblTreinador.class.php');
	$tblTreinador = new dbTblTreinador();
	$tblTreinador->set_IDMAIN('');
	$tblTreinador->set_IDUSER('');
	$tblTreinador->set_IDASSOC('');
	
	$tblAutoTpl->addObject('treinador',$tblTreinador);
	
	//Adds a new kind of Markup
	$tblAutoTpl->setMarkupType('pico');
	// When the Markup is called, executes callback function, overwriting the "TobjAutoTemplate" post actions.
	// Accepts a created Object "$tblTreinador" or a name "addObject('treinador',$tblTreinador)" as param.
	$tblAutoTpl->setCallback('pico','treinador','testeNew2',array('"Callback Object PICO"'));
	$tblAutoTpl->setCallback('pico',$tblTreinador,'testeNew2',array('"Callback Class PICO"'));
	
	// Also replace other vars as a simple template system
	$tblAutoTpl->setVar('{% pico2 %}','Sou uma variável!');

	function FunccaoNormal($parametro)
	{
		return '<br/>FunccaoNormal('.$parametro.')';
	}
	
	// Calling a File
	<?php
  	/*
  	* tpl_page($AsData,$AisFile=true,$AbProcessFile=false)
  	*
  	* @AsData: Accepts a String or a File Path (string)
  	* @AisFile: State if it's a String or a File Path (true/false)
  	* @AbProcessFile: Process File as a normal PHP (true/false)
  	*/
	  echo $tblAutoTpl->tpl_page($path.'ui/demohtml.php',true,true);
	?>
	
	//FILE: demohtml.php
	
		<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nome</th>
				
				// Executes a Function from a Created Object
				{func obj="treinador" func="getListSearch" params="[array;array;'nome';'ASC']"}{/func}
				
				// Executes a Function from a Created Object
				{hook obj="treinador" func="getListSearch" params="[array;array;'nome';'ASC']"}{/hook}
				
				// Executes a Function from a Created Object AND echo the result in this position
				{echo class="dbTblTreinador" func="testeNew3" params="['Markup novo PICO']"}{/echo}
				
				// Executes a normal Function AND echo the result in this position
				{echo func="FunccaoNormal" params="['Markup novo PICO']"}{/echo}
				
				// Executes a Function from a Created Object AND echo the result in this position
				{hook obj="treinador" func="testeNew" params="[]"}{/hook}
				
				// Executes a Function from a Created Object AND echo the result in this position
				{func obj="treinador" func="testeNew" params="[]"}{/func}
				
				// Executes a Function from a Created Object AND echo the result in this position
				// JSON (url-encoded) params are passed into the Function
				{echo obj="treinador" func="testeNew2" jsonparams="%5B%5B1,2,3%5D%2C%5B4,5,6%5D%2C%22nomejson%22%2C%22ASCJSON%22%5D" jsonencode="urlencode"}3{/echo}
			</tr>
		</thead>
		<tbody>
		  // Executes a Function from a Created Object AND replace the values inside the "loop" tags.
		  // return array['ROW'] = array(0=>['id'=>'123', 'nome'=>'Paulo José Mota'],
		  //                             1=>['id'=>'456', 'nome'=>'Pedro Miguel Mota']);
			{loop obj="treinador" func="getListSearch" params="[array;array;'nome';'ASC']"}
			<tr>
			  <!-- echo: 1, 2, 3, 4 -->
			  <td>{i}</td>
			  <!-- echo: mt_rand(); -->
			  <td>{random}</td>
				<td>{id}</td>
				<td>{nome}</td>
			</tr>
			{/loop}
		</tbody>
	</table>
=====

| ID  | Name |
| ------------- | ------------- |
| 123 | Paulo José Mota  |
| 456 | Pedro Miguel Mota  |

| Row  | Random | ID  | Name |
| ------------- | ------------- | ------------- | ------------- |
| 1 | xF62bGF81Mj | 123 | Paulo José Mota  |
| 2 | PoF93d0aJO | 456 | Pedro Miguel Mota  |
| 123 | Paulo José Mota  |
| 456 | Pedro Miguel Mota  |
