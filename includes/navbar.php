<?php $u = current_user(); ?><nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container"><a class="navbar-brand fw-bold text-success" href="/aasra/">AASRA</a><button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2"><?php if (!$u): ?><li class="nav-item"><a class="nav-link" href="/aasra/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/aasra/index.php#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="/aasra/index.php#how">How It Works</a></li>
                    <li class="nav-item"><a class="btn btn-outline-success" href="/aasra/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-success" href="/aasra/register.php">Register</a></li><?php else: ?><li class="nav-item"><span class="nav-link">Hi, <?= e($u['name']) ?></span></li>
                    <li><a class="btn btn-outline-danger btn-sm" href="/aasra/logout.php">Logout</a></li><?php endif; ?>
            </ul>
        </div>
    </div>
</nav>