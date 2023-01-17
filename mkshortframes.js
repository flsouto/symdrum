async function main(){

    const gd = require('node-gd');
    const exec = require('child_process').execSync;
    const fs = require('fs');

    if(!process.argv[3]){
        console.error("Usage: cmd <dir> <index>");
        process.exit();
    }

    const dir = process.argv[2].replace(/\/$/,"") + '/';
    const index = process.argv[3];
    const out_dir = dir + index + '_short_frames/';

    if(!fs.existsSync(out_dir)){
        fs.mkdirSync(out_dir,'0777');
    }

    try{
        exec("rm "+out_dir+"*.jpg 2>&1");
    } catch(e){}

    const wavs = {};
    const wavs_flat = [];
    for(const f of fs.readdirSync(dir)){
        if(!f.match(/.wav$/)){
            continue;
        }
        const i = f.split('_')[0]
        if(!wavs[i]) {
            wavs[i] = {
                sym: dir + i + '_sym.wav',
                drm_sym: dir + i + '_drm_sym.wav',
                drm: dir + i + '_drm.wav',
            };
            wavs_flat.push(...Object.values(wavs[i]));
        }
    }

    const osc_sym_dir = dir + "/" + index + "_osc_sym/";
    const osc_drm_dir = dir + "/" + index + "_osc_drm/";
    const margin = 150;
    const box_dim = 1080 - (margin *2);

    async function draw_drm_sym(drm_img_f, sym_img_f, out_f, hl_f){
        const img = await gd.createTrueColor(1080,1080);
        const drm_img = await gd.openJpeg(drm_img_f);
        const sym_img = await gd.openJpeg(sym_img_f);
        drm_img.copyResampled(img, 0, 0, 0, 0, 1080/2, 1080, 1280, 720);
        drm_img.copyResampled(img, 1080/2, 0, 1080/2, 0, 1080, 1080, 1280, 720);
        sym_img.copyResampled(img, margin, margin, 0, 0, box_dim, box_dim, 1400, 1400);

        img.saveJpeg(out_f);
        sym_img.destroy();
//        drm_img.destroy();
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
        const hl = wavs_flat.find(f => {
            return f.match(index + '_sym.wav')
        })
        await draw_drm_sym(osc[0].drm, sym, out_f, hl);
    }

    for(const {sym, drm} of osc){
        const out_f = mk_out_f();
        console.log(out_f);
        const hl = wavs_flat.find(f => {
            return f.match(index + '_drm_sym.wav')
        })
        await draw_drm_sym(drm, sym, out_f, hl);
    }

    for(const {drm} of osc){
        const out_f = mk_out_f();
        console.log(out_f);
        const hl = wavs_flat.find(f => {
            return f.match(index + '_drm.wav')
        })
        await draw_drm_sym(drm, osc[osc.length-1].sym, out_f, hl);
    }


}

main();
