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
    const out_dir = dir + index + '_frames/';

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

        const rgb = 200 + Math.round(Math.random() * 55);
        const fc1 = drm_img.colorAllocate(rgb, rgb, rgb);
        const fc2 = drm_img.colorAllocate(25, 25, 25);
        const spc = 10;
        const fsz = Math.round(box_dim / (wavs_flat.length+1)) - spc;

        for(let i=1;i<=wavs_flat.length;i++){
            const w = wavs_flat[i-1];
            const fc = w === hl_f ? fc1 : fc2;
            drm_img.stringFT(
                fc, './font.ttf', fsz, 0,
                box_offset_x + spc, box_offset_y + spc + ((fsz+spc)*i),
                wavs_flat[i-1]
            );
        }

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
