async function main(){

    const gd = require('node-gd');
    const exec = require('child_process').execSync;
    const fs = require('fs');

    if(!process.argv[3]){
        console.error("Usage: cmd <dir> <index>");
        process.exit();
    }

    const dir = process.argv[2];
    const index = process.argv[3];
    const out_dir = dir + '/' +index+'_frames' + '/';
    if(!fs.existsSync(out_dir)){
        fs.mkdirSync(out_dir,'0777');
    }

    try{
        exec("rm "+out_dir+"*.jpg 2>&1");
    } catch(e){}

    const osc_sym_dir = dir + "/" + index + "_osc_sym/";
    const osc_drm_dir = dir + "/" + index + "_osc_drm/";
    const margin = 80;
    const box_dim = 720 - (margin*2);
    const box_color = await gd.trueColorAlpha(0,0,0,25);

    async function draw_drm_sym(drm_img_f, sym_img_f, out_f){
        const drm_img = await gd.openJpeg(drm_img_f);
        const sym_img = await gd.openJpeg(sym_img_f);
        sym_img.copyResampled(drm_img, margin, margin, 0, 0, box_dim, box_dim, 1400, 1400);
        drm_img.filledRectangle(box_dim + margin, margin, box_dim + margin + box_dim, margin + box_dim, box_color);
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
        console.log(out_f);
        await draw_drm_sym(drm, osc[osc.length-1].sym, out_f);
    }


}

main();
