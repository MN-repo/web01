<?php $to_root = $at_root ? "" : ".."; ?>
		<nav>
			<details>
				<summary>
					<svg viewBox="0 0 100 80" width="40" height="40">
						<title>Menu</title>
						<rect width="100" height="20"></rect>
						<rect y="30" width="100" height="20"></rect>
						<rect y="60" width="100" height="20"></rect>
					</svg>
				</summary>

				<ul>
					<li><a href="<?php echo $to_root; ?>/">Home</a></li>
					<li><a href="<?php echo $to_root; ?>/faq">FAQ</a></li>
					<li><a href="<?php echo $to_root; ?>/notify_signup">Newsletter signup</a></li>
					<li><a href="https://soprani.ca/pipermail/jmp-news/">Newsletter archives</a></li>
					<li><a href="<?php echo $to_root; ?>/upgrade1">Pay for your account</a></li>
					<li><a href="<?php echo $to_root; ?>/credits">Credits and source code</a></li>
				</ul>
			</details>
		</nav>
