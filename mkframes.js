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

    for(const f of fs.readdirSync(osc_sym_dir)){

        if(!f.match(/jpg/)){
            continue;
        }

        console.log(f);

        const sym_img_f = osc_sym_dir + f;
        const drm_img_f = osc_drm_dir + f;
        if(!fs.existsSync(drm_img_f)){
            console.error('corresponding drm file not found: '+drm_img_f);
            process.exit();
        }

        const drm_img = await gd.openJpeg(drm_img_f);
        const sym_img = await gd.openJpeg(sym_img_f);

        const box_dim = 720 - (margin*2);
        const box_color = await gd.trueColorAlpha(0,0,0,25);
        sym_img.copyResampled(drm_img, margin, margin, 0, 0, box_dim, box_dim, 1400, 1400);
        drm_img.filledRectangle(box_dim + margin, margin, box_dim + margin + box_dim, margin + box_dim, box_color);
//        drm_img.copyResampled(box, box_dim + margin, margin, 0, 0, box_dim, box_dim, 1400, 1400);

        const out_f = out_dir + f;
        drm_img.saveJpeg(out_f);

        drm_img.destroy();
        sym_img.destroy();
    }

}

main();
