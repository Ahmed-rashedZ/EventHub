from PIL import Image

def remove_background(img_path, out_path, target_color=(13, 17, 23, 255)):
    img = Image.open(img_path).convert("RGBA")
    datas = img.getdata()
    
    new_data = []
    # Assume the top-left pixel is the background color
    bg_color = datas[0]
    
    for item in datas:
        # If pixel is close to background color, make it transparent
        if abs(item[0] - bg_color[0]) < 15 and abs(item[1] - bg_color[1]) < 15 and abs(item[2] - bg_color[2]) < 15:
            # new_data.append((255, 255, 255, 0)) # transparent
            new_data.append(target_color) # Or use solid #0d1117
        else:
            # If we are blending, we might want to do edge detection, but simple threshold is a start.
            # Let's just make the background exactly #0d1117.
            new_data.append(item)
            
    img.putdata(new_data)
    img.save(out_path, "PNG")
    print(f"Saved to {out_path}")

# Try processing the current logo.png which is white
remove_background(r"c:\xampp\htdocs\EventHub\EventHub\public\images\logo.png", r"c:\xampp\htdocs\EventHub\EventHub\public\images\logo_fixed.png")
