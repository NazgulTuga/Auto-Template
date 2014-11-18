#Template System
###A very Simple and Powerful template system!

```sh
	require_once('TobjAutoTemplate.class.php');
	$tblAutoTpl = new TobjAutoTemplate();
```
```sh
	/* TABLE CLASS */
	require_once('dbTblTreinador.class.php');
	$tblTreinador = new dbTblTreinador();
	
	$tblAutoTpl->*addObject*('treinador',$tblTreinador);
```

####Adds a new kind of Markup
```sh
	$tblAutoTpl->setMarkupType('pico');
```

 - When the Markup (pico) is called, executes callback function, overwriting the "TobjAutoTemplate" post actions.
 - Accepts a created Object "$tblTreinador" or a name "addObject('treinador',$tblTreinador)" as param.

```sh
	$tblAutoTpl->setCallback('pico','treinador','testeNew2',array('"Callback Object PICO"'));
	$tblAutoTpl->setCallback('pico',$tblTreinador,'testeNew2',array('"Callback Class PICO"'));
```

 - Also replace other vars as a simple template system

```sh
	$tblAutoTpl->setVar('{% pico2 %}','Sou uma variável!');
```
 - Calling a File

```sh
    /* public function renderize($AsFileHTML,$AisFile=true,$AbProcessFile=false,$AbCleanVars=false) */
  	$html = $tblAutoTpl->renderize('demohtml.php',true,true);
```

#Exemple: **demohtml.php**
	
```sh
		<?php /* execute some php code */ ?>
		<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nome</th>
				
				<!-- Executes a Function from a Created Object -->
				{func **obj**="treinador" **func**="search" **params**="[array;array;'nome';'ASC']"}{/func}
				
				<!-- Executes a Function from a Created Object -->
				{hook obj="treinador" func="search" params="[array;array;'nome';'ASC']"}{/hook}
				
				<!-- Executes a Function from a given Class AND echo the result in this position -->
				<!-- Dosen\'t need to be created! -->
				{echo **class**="dbTblTreinador" func="testeNew3" params="['Markup novo PICO']"}{/echo}
				
				<!-- Executes a normal Function AND echo the result in this position -->
				{echo **func**="FunccaoNormal" **params**="['Markup novo PICO']"}{/echo}
				
				<!-- Executes a Function from a Created Object -->
				{hook obj="treinador" func="testeNew" params="[]"}{/hook}
				
				<!-- Executes a Function from a Created Object -->
				{func obj="treinador" func="testeNew" params="[]"}{/func}
				
				<!-- Executes a Function from a Created Object AND echo the result in this position -->
				<!-- JSON (url-encoded) params are passed into the Function -->
				{echo obj="treinador" func="testeNew2" **jsonparams**="%5B%5B1,2,3%5D%2C%5B4,5,6%5D%2C%22nomejson%22%2C%22ASCJSON%22%5D" **jsonencode**="urlencode"}3{/echo}
			</tr>
		</thead>
		<tbody>
		  <!-- Executes a Function from a Created Object AND replace the values inside the "loop" tags. -->
		  <!-- return array = array(0=>['id'=>'123', 'name'=>'Paulo José Mota'], -->
		  <!--                      1=>['id'=>'456', 'name'=>'Pedro Miguel Mota']); -->
			{loop obj="treinador" func="search" params="['name';'P']"}
			<tr>
			  	<td>{i}</td> <!-- echo: 1, 2, 3, 4 (Automaticaly) -->
			  	<td>{random}</td> <!-- echo: mt_rand(); (Automaticaly) -->
				<td>{id}</td>
				<td>{nome}</td>
			</tr>
			{/loop}
		</tbody>
	</table>
```

| ID  | Name |
| ------------- | ------------- |
| 123 | Paulo José Mota  |
| 456 | Pedro Miguel Mota  |

| Row  | Random | ID  | Name |
| ------------- | ------------- | ------------- | ------------- |
| 1 | xF62bGF81Mj | 123 | Paulo José Mota  |
| 2 | PoF93d0aJO | 456 | Pedro Miguel Mota  |
