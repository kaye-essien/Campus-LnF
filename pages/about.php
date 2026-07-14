<?php
session_start();
$pageTitle = 'About';
?>
<?php include '../includes/header.php'; ?>
<div class="container" style="max-width:780px">
    <div style="text-align:center;padding:3rem 1rem 2rem">
        <div style="font-size:3rem">🔍</div>
        <h1 style="font-size:2rem;margin-top:0.5rem">About Campus<span style="color:var(--accent2)">L&F</span></h1>
        <p style="color:var(--muted);font-size:1.05rem;max-width:520px;margin:0.75rem auto 0">UMaT's student-run lost & found platform — helping the campus community recover what matters.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:3rem">
        <?php foreach ([
            ['📋','Report','Lost something? Post it in seconds. Found something? Help return it to its owner.'],
            ['🔎','Browse','Search all open reports by title, location, category, or type.'],
            ['✋','Claim','Think you found the owner? Submit a claim and connect directly.'],
            ['✅','Resolve','Once reunited, mark the item as resolved and close the report.'],
        ] as [$icon,$title,$desc]): ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;text-align:center">
            <div style="font-size:2rem;margin-bottom:0.5rem"><?= $icon ?></div>
            <h3 style="margin-bottom:0.5rem"><?= $title ?></h3>
            <p style="color:var(--muted);font-size:0.9rem"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:2rem">
        <h2 style="margin-bottom:1rem">How it works</h2>
        <?php foreach ([
            ['1','Register with your UMaT student email (@st.umat.edu.gh)'],
            ['2','Report a lost or found item with photos, description and location'],
            ['3','Other students browse and search the listings'],
            ['4','If someone recognises the item they submit a claim'],
            ['5','The reporter reviews the claim and approves or rejects it'],
            ['6','Once resolved the item is marked as returned'],
        ] as [$num,$step]): ?>
        <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1rem">
            <div style="background:var(--accent2);color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;flex-shrink:0"><?= $num ?></div>
            <p style="margin:0;padding-top:0.25rem"><?= $step ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;margin-bottom:2rem">
        <h2 style="margin-bottom:0.75rem">Rules & Guidelines</h2>
        <ul style="color:var(--muted);font-size:0.95rem;line-height:2;padding-left:1.25rem">
            <li>Only UMaT students with valid @st.umat.edu.gh emails can register</li>
            <li>Only report items genuinely lost or found on UMaT campus</li>
            <li>Do not submit false claims — this wastes everyone's time</li>
            <li>Be respectful and honest in all communications</li>
            <li>Admins reserve the right to remove any misleading posts</li>
        </ul>
    </div>

    <div style="text-align:center;padding:2rem 0">
        <a href="/pages/register.php" class="btn btn-primary" style="padding:0.75rem 2rem;font-size:1rem">Get Started →</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
