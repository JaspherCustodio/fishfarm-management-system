<nav class="side-bar">
    <!-- admin navigation bar -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") { ?>

    <ul id="navList">
        <!-- MAIN -->
        <li class="menu-main">
            <a href="../nav/dashboard.php"><i class="fa-solid fa-gauge"></i></i><span>Dashboard</span></a>
        </li>
        <hr>
        <li class="menu-main">
            <a href="../nav/expense.php"><i class="fa-solid fa-dollar-sign"></i><span>Manage Expense</span></a>
        </li>
        <li class="menu-main">
            <a href="../nav/fish_cage.php"><i class="fa-solid fa-fish"></i></i><span>Fish Cage</span></a>
        </li>
        <li class="menu-main">
            <a href="../nav/scheduler.php"><i class="fa-regular fa-calendar"></i></i><span>Scheduler</span></a>
        </li>
        

        <hr>

        <!-- SECTION LABEL -->
        <li class="menu-section">RECORDS MANAGEMENT</li>

        <!-- SUB -->

        <li class="menu-sub">
            <a href="../nav/fish_cage_management.php">
                <i class="fa-solid fa-list"></i>
                <span>Fish Cage Management</span>
            </a>
        </li>

        <li class="menu-sub">
            <a href="../nav/sampling.php">
                <i class="fa-solid fa-fish"></i>
                <span>Sampling</span>
            </a>
        </li>

        <li class="menu-sub">
            <a href="../nav/feeding.php">
                <i class="fa-solid fa-bowl-food"></i>
                <span>Feeding</span>
            </a>
        </li>

        <li class="menu-sub overlay-parent">
            <a href="javascript:void(0)" class="overlay-trigger"><i class="fa-solid fa-boxes-stacked"></i><span>Stocking/Transferring</span><i class="fa-solid fa-chevron-right arrow"></i></a>

            <div class="overlay-menu">
                <a href="../nav/stocking.php">Stocking</a>
                <a href="../nav/transferring.php">Transferring</a>
                <a href="../nav/delivering.php">Delivering</a>
            </div>
        </li>

        <li class="menu-sub overlay-parent">
            <a href="javascript:void(0)" class="overlay-trigger">
                <i class="fa-regular fa-calendar-days"></i>
                <span>Fish Net Maintenance</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <div class="overlay-menu">
                <a href="../nav/net_cleaning.php">Net Cleaning</a>
                <a href="../nav/net_repairing.php">Net Repairing</a>
                <a href="../nav/net_checking.php">Net Checking</a>
            </div>
        </li>

        <hr>

        <!-- MAIN -->
        <li class="menu-main">
            <a href="../nav/user_management.php"><i class="fa-solid fa-users"></i><span>User Management</span></a>
        </li>
    </ul>
    <?php }else{ ?>
    <ul>
        <!-- MAIN -->
        <li class="menu-main">
            <a href="../nav/dashboard.php"><i class="fa-solid fa-gauge"></i></i><span>Dashboard</span></a>
        </li>


        <hr>

        <!-- SECTION LABEL -->
        <li class="menu-section">RECORDS MANAGEMENT</li>

        <!-- SUB -->

        <li class="menu-sub">
            <a href="../nav/fish_cage_management.php">
                <i class="fa-solid fa-list"></i>
                <span>Fish Cage Management</span>
            </a>
        </li>

        <li class="menu-sub">
            <a href="../nav/sampling.php">
                <i class="fa-solid fa-fish"></i>
                <span>Sampling</span>
            </a>
        </li>

        <li class="menu-sub">
            <a href="../nav/feeding.php">
                <i class="fa-solid fa-bowl-food"></i>
                <span>Feeding</span>
            </a>
        </li>

        <li class="menu-sub overlay-parent">
            <a href="javascript:void(0)" class="overlay-trigger"><i class="fa-solid fa-boxes-stacked"></i><span>Stocking/Transferring</span><i class="fa-solid fa-chevron-right arrow"></i></a>

            <div class="overlay-menu">
                <a href="../nav/stocking.php">Stocking</a>
                <a href="../nav/transferring.php">Transferring</a>
                <a href="../nav/delivering.php">Delivering</a>
            </div>
        </li>

        <li class="menu-sub overlay-parent">
            <a href="javascript:void(0)" class="overlay-trigger">
                <i class="fa-regular fa-calendar-days"></i>
                <span>Fish Net Maintenance</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <div class="overlay-menu">
                <a href="../nav/net_cleaning.php">Net Cleaning</a>
                <a href="../nav/net_repairing.php">Net Repairing</a>
                <a href="../nav/net_checking.php">Net Checking</a>
            </div>
        </li>
    </ul>
<?php } ?>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const currentPage = window.location.pathname.split("/").pop();

    document.querySelectorAll(".side-bar a").forEach(link => {

        const linkPage = link.getAttribute("href")
            ?.split("/")
            .pop()
            .split("?")[0];

        if (linkPage === currentPage) {

            // Highlight only the exact link
            link.classList.add("active-link");

            // If inside overlay, open parent
            const overlayParent = link.closest(".overlay-parent");
            if (overlayParent) {
                overlayParent.classList.add("open");
                overlayParent.classList.add("active-parent");
            }
        }
    });

});
    
document.querySelectorAll('.overlay-trigger').forEach(trigger => {
    trigger.addEventListener('click', e => {
        e.preventDefault();
        const parent = trigger.closest('.overlay-parent');

        // close others
        document.querySelectorAll('.overlay-parent.open')
            .forEach(p => p !== parent && p.classList.remove('open'));

        parent.classList.toggle('open');
    });
});

// close when clicking outside
document.addEventListener('click', e => {

    // ✅ DO NOT CLOSE if clicking inside a modal
    if (e.target.closest('.modal') || e.target.closest('.modal-overlay')) {
        return;
    }

    if (!e.target.closest('.overlay-parent')) {
        document.querySelectorAll('.overlay-parent.open')
            .forEach(p => p.classList.remove('open'));
    }
});

</script>

