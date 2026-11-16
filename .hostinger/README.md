# Inrich Media production deployment

The production domain is https://inrichmedia.in/. This deployment uses the exact build published at https://darshannahata5555-cpu.github.io/inrich-media/, pinned to the commit in `source.json`.

The deployment normalizes the GitHub Pages `/inrich-media` base path to the domain root in HTML, JavaScript, CSS and Next.js navigation payloads. Normal pages allow search indexing; error pages retain noindex. All layout, copy, images and animations come from the linked build.

`deploy.php` verifies the source archive checksum, stages all 230 files, backs up overwritten files outside the document root and publishes the homepage last. The existing WordPress files, subdomains and root routing remain in place. A completion marker prevents rerunning the deployment. The temporary Hostinger task is removed once deployment succeeds.

Deployment workspace and backup: `/home/u128113746/domains/inrichmedia.in/.inrich-release-20261116-ec03752/`.
