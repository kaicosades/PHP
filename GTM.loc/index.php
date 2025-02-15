<?php
session_start();
require_once("components/head.php"); ?>

<body>
	<?php require_once("components/header_login.php"); ?>
	<div class="column">
		<selection>
			<?php
			if (isset($_GET["page"])) {
				$page = basename($_GET["page"]); // Оставляет только имя файла/удаление путей
				$file = "components/{$page}.php";

				if (file_exists($file)) {
					include_once($file);
				} else {
					echo "Страница не найдена.";
				}
			}

			?>
		</selection>
	</div>
</body>

</html>