async function main(){

    const gd = require('node-gd');
    const exec = require('child_process').execSync;
    const fs = require('fs');

    if(!process.argv[2]){
        console.error("Usage: cmd <dir>");
        process.exit();
    }

    const dir = process.argv[2].replace(/\/$/,"") + '/';
    const out_dir = dir + 'covers/';
    const dummy = dir + '01_drm.wav';
    const size = parseFloat(exec("soxi -D "+dummy).toString());
    const BPM = Math.round(120 * size / 16);

    if(!fs.existsSync(out_dir)){
        fs.mkdirSync(out_dir,'0777');
    }

    try{
        exec("rm "+out_dir+"*.jpg 2>&1");
    } catch(e){}

    const osc_sym_dir = dir + "/01_osc_sym/";
    const osc_drm_dir = dir + "/01_osc_drm/";
    const margin = 80;
    const box_dim = 720 - (margin*2);
    const box_color = await gd.trueColorAlpha(0,0,0,25);
    const box_color2 = await gd.trueColorAlpha(0,0,0,50);

    async function draw_drm_sym(drm_img_f, sym_img_f, out_f, hl_f){
        const drm_img = await gd.openJpeg(drm_img_f);
        const sym_img = await gd.openJpeg(sym_img_f);
        sym_img.copyResampled(drm_img, margin, margin, 0, 0, box_dim, box_dim, 1400, 1400);

        const box_offset_x = box_dim + margin;
        const box_offset_y = margin;

        drm_img.filledRectangle(box_offset_x, box_offset_y, box_offset_x + box_dim - margin, margin + box_dim, box_color);
        drm_img.filledRectangle(box_offset_x + box_dim - margin, box_offset_y, box_offset_x + box_dim, margin + box_dim, box_color2);

        const r = Math.round(Math.random() * 255);
        const g = Math.round(Math.random() * 255);
        const b = Math.round(Math.random() * 255);
        const fc1 = drm_img.colorAllocate(r, g, b);
        const fc2 = drm_img.colorAllocate(25, 25, 25);
        const spc = 10;

        drm_img.stringFT(
            fc1, './font-bold.ttf', 180, 0,
            box_offset_x + margin, 350,
            String(BPM)
        );

        drm_img.stringFT(
            fc1, './font-bold.ttf', 180, 0,
            box_offset_x + margin,  550,
            'BPM'
        );

        let t = 35;
        drm_img.setThickness(t);
        t = 8;
        drm_img.line(0, t, 1280, t, fc1);
        drm_img.line(1280-t, t, 1280 -t, 720-t, fc1);
        drm_img.line(1280, 720-t, 0, 720-t, fc1);
        drm_img.line(t, 720-t, t, t, fc1);

        drm_img.saveJpeg(out_f);
        sym_img.destroy();
        drm_img.destroy();
    }

    const osc = [];

    for(const f of fs.readdirSync(osc_sym_dir)){

        if(!f.match(/jpg/)){
            continue;
        }

        const sym_img_f = osc_sym_dir + f;
        const drm_img_f = osc_drm_dir + f;

        if(!fs.existsSync(drm_img_f)){
            console.error('corresponding drm file not found: '+drm_img_f);
            process.exit();
        }
        osc.push({sym:sym_img_f,drm:drm_img_f});
    }

    let i = 0;
    const mk_out_f = () => {
        return out_dir + String(++i).padStart(4,'0') + '.jpg';
    }

    for(const {sym} of osc){
        const out_f = mk_out_f();
        console.log(out_f);
        await draw_drm_sym(osc[0].drm, sym, out_f);
    }

    for(const {sym, drm} of osc){
        const out_f = mk_out_f();
        console.log(out_f);
        await draw_drm_sym(drm, sym, out_f);
    }

    for(const {drm} of osc){
        const out_f = mk_out_f();
        await draw_drm_sym(drm, osc[osc.length-1].sym, out_f);
    }


}

main();
