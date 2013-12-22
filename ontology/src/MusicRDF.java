import java.io.InputStream;

import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.util.FileManager;



public class MusicRDF {
	public static void main(String[] args) {
		String input_filename = "music.owl";
		
		Model model = ModelFactory.createDefaultModel();
		InputStream in = FileManager.get().open(input_filename);
		if (in == null) {
			throw new IllegalArgumentException("File: " + input_filename + " not found");
		}
		
		model.read(in, null);
		model.write(System.out);
	}
}
