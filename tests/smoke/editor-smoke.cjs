const puppeteer = require("puppeteer-core");

(async () => {
    const browser = await puppeteer.launch({
        executablePath: "C:\\Program Files\\Mozilla Firefox\\firefox.exe",
        headless: true,
        args: ["-no-sandbox", "-disable-dev-shm-usage", "-disable-gpu"],
    });
    const page = await browser.newPage();
    page.on("console", (msg) => console.log("[" + msg.type() + "]", msg.text()));
    page.on("pageerror", (err) => console.log("[pageerror]", err.message));
    page.on("requestfailed", (req) =>
        console.log("[reqfail]", req.url(), req.failure()?.errorText),
    );

    const url = "http://127.0.0.1:8765/admin/posts/create";
    console.log("navigating to", url);
    try {
        const resp = await page.goto(url, { waitUntil: "load", timeout: 20000 });
        console.log("status:", resp.status());
        await new Promise((r) => setTimeout(r, 5000));

        const probe = await page.evaluate(() => {
            const root = document.querySelector(".milkdown-host");
            const pm = document.querySelector(".ProseMirror");
            const editorRoot = document.querySelector('[x-ref="root"]');
            const errEl = editorRoot ? editorRoot.querySelector(".text-red-600") : null;
            return {
                hasMilkdownHost: !!root,
                hasProseMirror: !!pm,
                hasErrorBox: !!errEl,
                errorText: errEl ? errEl.innerText.substring(0, 800) : null,
            };
        });
        console.log("PROBE:", JSON.stringify(probe, null, 2));
    } catch (e) {
        console.log("navigation error:", e.message);
    }

    await browser.close();
})().catch((e) => {
    console.error("TEST FAILED:", e);
    process.exit(1);
});
